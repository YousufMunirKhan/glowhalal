<?php

namespace App\Filament\Widgets;

use App\Models\InventoryItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Low stock')
            ->query(
                InventoryItem::query()
                    ->with(['variant.product:id,name', 'location:id,name'])
                    ->whereColumn('quantity_available', '<=', 'reorder_level')
                    ->orderBy('quantity_available')
            )
            ->emptyStateHeading('Everything is above its reorder level')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->columns([
                TextColumn::make('variant.product.name')
                    ->label('Product')
                    ->wrap()
                    ->description(fn (InventoryItem $record) => $record->variant?->name),
                TextColumn::make('variant.sku')->label('SKU')->copyable(),
                TextColumn::make('quantity_available')
                    ->label('Available')
                    ->badge()
                    ->color(fn ($state) => (int) $state <= 0 ? 'danger' : 'warning'),
                TextColumn::make('reorder_level')->label('Reorder at'),
                TextColumn::make('reorder_quantity')->label('Reorder qty'),
            ])
            ->paginated([5, 10]);
    }
}
