<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Forms\MoneyInput;
use App\Support\Money;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Variants own SKUs, prices and inventory rows, so they get a table with their
 * own filters and per-row actions rather than a Repeater.
 */
class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Variants';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Variant name')
                        ->required()
                        ->maxLength(150)
                        ->helperText('The shade or size, e.g. "Nude Rose" or "50 ml".'),

                    TextInput::make('sku')
                        ->label('SKU')
                        ->required()
                        ->maxLength(64)
                        ->unique(ignoreRecord: true),
                ]),

                Grid::make(3)->schema([
                    // Stored as integer paisa; entered and displayed in rupees.
                    MoneyInput::make('price_amount', 'Price')->required(),

                    MoneyInput::make('compare_at_amount', 'Compare at')
                        ->helperText('Must be at or above the price — enforced by the database.'),

                    MoneyInput::make('cost_amount', 'Unit cost'),
                ]),

                Grid::make(3)->schema([
                    TextInput::make('barcode')->maxLength(64),
                    TextInput::make('weight_grams')->numeric()->minValue(0)->default(0),
                    TextInput::make('position')->numeric()->minValue(0)->default(0),
                ]),

                Select::make('attributeValues')
                    ->label('Options')
                    ->relationship('attributeValues', 'value')
                    ->multiple()
                    ->preload()
                    ->helperText('Shade, size and other variant-defining option values.'),

                Grid::make(4)->schema([
                    Toggle::make('is_active')->default(true),
                    Toggle::make('is_default')
                        ->helperText('Exactly one per product; setting this clears the others.'),
                    Toggle::make('track_inventory')->default(true),
                    Toggle::make('allow_backorder'),
                ]),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('position')->label('#')->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('sku')->label('SKU')->searchable()->copyable(),
                TextColumn::make('price_amount')
                    ->label('Price')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => MoneyInput::format($state)),
                TextColumn::make('inventory.quantity_available')
                    ->label('Available')
                    ->badge()
                    ->placeholder('—')
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        (int) $state === 0 => 'danger',
                        (int) $state <= 5 => 'warning',
                        default => 'success',
                    }),
                IconColumn::make('is_default')->label('Default')->boolean(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->defaultSort('position')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
