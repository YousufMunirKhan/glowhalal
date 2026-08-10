<?php

namespace App\Filament\Resources\InventoryItems\Schemas;

use App\Models\InventoryLocation;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InventoryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Pick the product variant from a searchable list — never a raw ID.
                Select::make('product_variant_id')
                    ->label('Product variant')
                    ->relationship('variant', 'name')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $product = $record->product?->name;
                        $variant = $record->name ?: $record->sku ?: ('Variant #'.$record->getKey());

                        return $product ? "{$product} — {$variant}" : $variant;
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                // Defaults to the warehouse marked as default, so the owner never
                // faces an empty required dropdown.
                Select::make('inventory_location_id')
                    ->label('Inventory location')
                    ->relationship('location', 'name')
                    ->default(fn () => InventoryLocation::where('is_default', true)->value('id')
                        ?? InventoryLocation::query()->value('id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('quantity_on_hand')
                    ->label('Quantity on hand')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                TextInput::make('quantity_reserved')
                    ->label('Quantity reserved')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Units already promised to open orders.'),

                // quantity_available is a database-generated column
                // (on hand − reserved). It is intentionally NOT an input here —
                // editing it would be ignored by the model anyway.

                TextInput::make('reorder_level')
                    ->label('Reorder level')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(5)
                    ->helperText('When available stock drops to this, the item is flagged as low.'),
                TextInput::make('reorder_quantity')
                    ->label('Reorder quantity')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(20),
                DateTimePicker::make('last_counted_at')
                    ->label('Last counted at'),
            ]);
    }
}
