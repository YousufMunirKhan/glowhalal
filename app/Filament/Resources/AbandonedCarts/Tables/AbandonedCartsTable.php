<?php

namespace App\Filament\Resources\AbandonedCarts\Tables;

use App\Models\Cart;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AbandonedCartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('items')
                    ->label('In cart')
                    ->state(fn (Cart $record) => $record->itemsSummary())
                    ->wrap()
                    ->limit(60),

                TextColumn::make('grand_total_amount')
                    ->label('Value')
                    ->state(fn (Cart $record) => $record->grand_total_amount?->format())
                    ->sortable(),

                TextColumn::make('last_activity_at')
                    ->label('Abandoned')
                    ->since()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('whatsapp')
                    ->label('Message on WhatsApp')
                    ->icon(Heroicon::ChatBubbleLeftRight)
                    ->color('success')
                    ->url(fn (Cart $record) => $record->whatsappRecoveryUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (Cart $record) => $record->whatsappRecoveryUrl() !== null),

                Action::make('mark_contacted')
                    ->label('Mark contacted')
                    ->icon(Heroicon::Check)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('This removes the cart from the recovery list so it is not messaged again.')
                    ->action(fn (Cart $record) => $record->update(['recovery_contacted_at' => now()])),
            ])
            ->emptyStateHeading('No carts to recover')
            ->emptyStateDescription('Carts where someone entered their phone but did not order will appear here ~20 minutes after they go quiet.')
            ->emptyStateIcon(Heroicon::OutlinedShoppingBag);
    }
}
