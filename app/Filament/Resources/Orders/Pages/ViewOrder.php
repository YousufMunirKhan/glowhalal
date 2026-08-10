<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Orders\OrderStateMachine;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = \App\Filament\Resources\Orders\OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirm')
                ->label('Confirm order')
                ->icon('heroicon-o-check-circle')->color('info')
                ->visible(fn (Order $record) => $record->status->canTransitionTo(OrderStatus::Confirmed))
                ->requiresConfirmation()
                ->modalDescription('Confirming commits stock for every line on this order.')
                ->schema([
                    Textarea::make('note')->label('Internal note')->rows(2),
                    Toggle::make('notify_customer')->default(true),
                ])
                ->action(fn (Order $record, array $data) => $this->run($record, OrderStatus::Confirmed, $data)),

            Action::make('ship')
                ->label('Mark shipped')
                ->icon('heroicon-o-truck')->color('primary')
                ->visible(fn (Order $record) => $record->status->canTransitionTo(OrderStatus::Shipped))
                ->schema([
                    TextInput::make('tracking_number')->required()->maxLength(80),
                    Select::make('courier')->required()->native(false)
                        ->options(['tcs' => 'TCS', 'leopards' => 'Leopards', 'mp' => 'M&P', 'postex' => 'PostEx', 'other' => 'Other']),
                    Toggle::make('notify_customer')->default(true),
                ])
                ->action(fn (Order $record, array $data) => $this->run($record, OrderStatus::Shipped, $data)),

            Action::make('deliver')
                ->label('Mark delivered')
                ->icon('heroicon-o-home')->color('success')
                ->visible(fn (Order $record) => $record->status->canTransitionTo(OrderStatus::Delivered))
                ->requiresConfirmation()
                ->modalDescription('For COD orders this also records payment as collected.')
                ->action(fn (Order $record) => $this->run($record, OrderStatus::Delivered)),

            Action::make('cancel')
                ->label('Cancel order')
                ->icon('heroicon-o-x-circle')->color('danger')
                ->visible(fn (Order $record) => $record->status->canTransitionTo(OrderStatus::Cancelled))
                ->requiresConfirmation()
                ->schema([
                    Select::make('cancel_reason')->required()->native(false)->options([
                        'customer_request' => 'Customer requested',
                        'out_of_stock' => 'Out of stock',
                        'undeliverable' => 'Address undeliverable',
                        'payment_failed' => 'Payment not received',
                        'suspected_fraud' => 'Suspected fraud',
                        // COD refusal rates are the single largest cost driver for
                        // Pakistani e-commerce — categorise it to manage it.
                        'cod_refused' => 'COD refused at door',
                    ]),
                    Textarea::make('note')->rows(2),
                    Toggle::make('restock')->label('Return items to stock')->default(true),
                ])
                ->action(fn (Order $record, array $data) => $this->run($record, OrderStatus::Cancelled, $data)),

            Action::make('refund')
                ->label('Record refund')
                ->icon('heroicon-o-arrow-uturn-left')->color('warning')
                ->visible(fn (Order $record) => $record->status->canTransitionTo(OrderStatus::Refunded))
                ->schema([
                    TextInput::make('amount')->numeric()->required()->prefix('Rs')
                        ->helperText('Full or partial. Entered in rupees.'),
                    Textarea::make('note')->rows(2)->required(),
                ])
                ->action(fn (Order $record, array $data) => $this->run($record, OrderStatus::Refunded, $data)),

            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * The domain re-checks the transition independently of the UI, so a forged
     * request is rejected even though the button was never rendered.
     */
    private function run(Order $record, OrderStatus $target, array $data = []): void
    {
        try {
            app(OrderStateMachine::class)->transition($record, $target, $data);

            Notification::make()
                ->success()
                ->title('Order '.$target->getLabel())
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->danger()
                ->title('Transition rejected')
                ->body($e->getMessage())
                ->send();
        }
    }
}
