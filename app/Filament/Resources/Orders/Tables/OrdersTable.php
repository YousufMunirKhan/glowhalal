<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Support\Identifier;
use App\Support\Money;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('items'))
            ->columns([
                // Order numbers are single unbreakable tokens — never let the
                // browser hyphenate through the middle of one.
                TextColumn::make('order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->extraAttributes(['style' => Identifier::style()]),

                // The email is truncated at the string level, not by CSS: an
                // unbroken address sets the column's minimum width, so no
                // max-width or width() can shrink the column while the full
                // value is still in the DOM. Full address is on the tooltip,
                // on the view page, and still fully searchable.
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn ($record) => Str::limit((string) $record->email, 24))
                    ->tooltip(fn ($record) => $record->email)
                    ->extraAttributes(['style' => Identifier::style()]),

                // Secondary columns. Below 1536px they are dropped rather than
                // pushed off the edge of a horizontally-scrolling table — at a
                // 1280px viewport the status column was scrolled out of sight,
                // which is the one column an operator actually scans for.
                TextColumn::make('items_count')->label('Items')->badge()->color('gray')
                    ->visibleFrom('2xl'),

                TextColumn::make('grand_total_amount')
                    ->label('Total')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state instanceof Money ? $state->format() : '—'),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cod' => 'COD',
                        'bank_transfer' => 'Bank transfer',
                        'jazzcash' => 'JazzCash',
                        'easypaisa' => 'Easypaisa',
                        default => $state,
                    })
                    ->color('gray')
                    ->visibleFrom('2xl'),

                TextColumn::make('payment_status')->label('Payment')->badge()->sortable(),

                TextColumn::make('status')->badge()->sortable(),

                // Date only. The time component added ~55px and pushed the row
                // actions off the edge at a 1280px viewport; the exact minute an
                // order was placed is on the view page.
                TextColumn::make('placed_at')
                    ->label('Placed')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable()
                    ->tooltip(fn ($record) => $record->placed_at?->format('d M Y H:i')),
            ])
            ->filters([
                SelectFilter::make('status')->options(OrderStatus::class)->multiple(),
                SelectFilter::make('payment_status')->options(PaymentStatus::class)->multiple(),
                SelectFilter::make('payment_method')->options([
                    'cod' => 'Cash on delivery',
                    'bank_transfer' => 'Bank transfer',
                    'jazzcash' => 'JazzCash',
                    'easypaisa' => 'Easypaisa',
                ]),
                Filter::make('open')
                    ->label('Open orders')
                    ->query(fn (Builder $q) => $q->whereIn('status', [
                        OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Shipped,
                    ])),
                Filter::make('awaiting_payment')
                    ->label('Awaiting payment verification')
                    ->query(fn (Builder $q) => $q->where('payment_status', PaymentStatus::AwaitingVerification)),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([ViewAction::make(), EditAction::make(), DeleteAction::make()]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
