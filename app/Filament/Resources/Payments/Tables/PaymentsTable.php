<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentAttemptStatus;
use App\Enums\PaymentStatus;
use App\Filament\Forms\MoneyInput;
use App\Filament\Support\Identifier;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Doubles as the bank-transfer verification queue (§4.7). Approving a proof is
 * the moment an order becomes payable, so it is an explicit action with a
 * recorded reviewer, not an inline edit.
 */
class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('order:id,order_number,email'))
            ->columns([
                TextColumn::make('order.order_number')->label('Order')->searchable()->copyable(),
                TextColumn::make('driver')->badge()->color('gray'),
                TextColumn::make('status')->badge(),
                TextColumn::make('amount')->formatStateUsing(fn ($state) => MoneyInput::format($state))->sortable(),
                // Bank references are unbreakable tokens; clip rather than
                // hyphenate, with the full value on the tooltip and copy button.
                TextColumn::make('reference')->placeholder('—')->searchable()->copyable()
                    ->tooltip(fn ($record) => $record->reference)
                    ->extraAttributes(['style' => Identifier::style()]),

                TextColumn::make('paid_at')->dateTime('d M Y H:i')->placeholder('—')->sortable(),

                // Dropped below 1536px — it is the least-scanned column and was
                // pushing the row actions past the edge at 1280px.
                TextColumn::make('verifiedBy.name')->label('Verified by')->placeholder('—')
                    ->visibleFrom('2xl'),
                TextColumn::make('created_at')->dateTime('d M Y H:i')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(PaymentAttemptStatus::class)->multiple(),
                SelectFilter::make('driver')->options([
                    'cod' => 'Cash on delivery',
                    'bank_transfer' => 'Bank transfer',
                    'jazzcash' => 'JazzCash',
                    'easypaisa' => 'Easypaisa',
                ]),
                Filter::make('to_verify')
                    ->label('Bank transfers to verify')
                    ->query(fn (Builder $q) => $q->where('driver', 'bank_transfer')
                        ->where('status', PaymentAttemptStatus::AwaitingVerification)),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    Action::make('approveProof')
                        ->label('Approve payment')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription('Marks this payment as received and credits the order.')
                        ->visible(fn (Payment $record) => $record->status === PaymentAttemptStatus::AwaitingVerification)
                        ->action(fn (Payment $record) => self::approve($record)),

                    Action::make('rejectProof')
                        ->label('Reject payment')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->schema([
                            Textarea::make('rejection_reason')->required()->rows(2),
                        ])
                        ->visible(fn (Payment $record) => $record->status === PaymentAttemptStatus::AwaitingVerification)
                        ->action(fn (Payment $record, array $data) => self::reject($record, $data['rejection_reason'])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function approve(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => PaymentAttemptStatus::Paid,
                'paid_at' => now(),
                'verified_by_user_id' => auth()->id(),
                'verified_at' => now(),
            ]);

            $payment->proofs()->where('status', 'pending')->update([
                'status' => 'approved',
                'reviewed_by_user_id' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            if ($order = $payment->order) {
                $order->paid_amount = $payment->amount;
                $order->payment_status = PaymentStatus::Paid;
                $order->save();
            }
        });

        Notification::make()->success()->title('Payment approved')->send();
    }

    private static function reject(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {
            $payment->update([
                'status' => PaymentAttemptStatus::Failed,
                'failure_message' => $reason,
                'verified_by_user_id' => auth()->id(),
                'verified_at' => now(),
            ]);

            $payment->proofs()->where('status', 'pending')->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'reviewed_by_user_id' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        Notification::make()->warning()->title('Payment rejected')->send();
    }
}
