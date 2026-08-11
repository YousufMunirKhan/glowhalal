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

    // "Variants" is developer jargon for a store that mostly sells simple
    // one-size products — the owner reads this as the place where price,
    // discount and stock live, so the tab says exactly that.
    protected static ?string $title = 'Price, sizes & stock';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basics')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Size / label')
                        ->required()
                        ->maxLength(150)
                        ->helperText('Jo customer ko dikhta hai — e.g. "50 ml" ya "100 ml".'),

                    TextInput::make('sku')
                        ->label('SKU (item code)')
                        ->required()
                        ->maxLength(64)
                        ->unique(ignoreRecord: true),
                ]),
            ]),

            Section::make('Price & discount')
                ->description('Discount dene ke liye: "Old price" mein purani qeemat likhein aur "Selling price" mein nayi — website, Google aur Facebook har jagah purani qeemat cut ho kar "Rs X off" ke sath dikhegi. Discount khatam karna ho to Old price khali kar dein.')
                ->schema([
                    Grid::make(2)->schema([
                        // Stored as integer paisa; entered and displayed in rupees.
                        MoneyInput::make('price_amount', 'Selling price')
                            ->required()
                            ->helperText('Jo qeemat customer ada karega.'),

                        MoneyInput::make('compare_at_amount', 'Old price (for discount)')
                            ->helperText('Optional — selling price se zyada honi chahiye, warna save nahi hogi.'),
                    ]),
                ]),

            Section::make('Advanced')
                ->description('Aam tor par inhe chherne ki zaroorat nahi.')
                ->collapsed()
                ->schema([
                    Grid::make(3)->schema([
                        MoneyInput::make('cost_amount', 'Unit cost (aap ki lagat)'),
                        TextInput::make('barcode')
                            ->maxLength(64)
                            ->helperText('Packaging ka asli barcode number — schema gtin isi se banta hai.'),
                        TextInput::make('weight_grams')->numeric()->minValue(0)->default(0),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('position')->numeric()->minValue(0)->default(0),
                        Select::make('attributeValues')
                            ->label('Options')
                            ->relationship('attributeValues', 'value')
                            ->multiple()
                            ->preload()
                            ->helperText('Sirf multi-variant products (shades waghera) ke liye. Simple product mein khali chhorein.'),
                    ]),

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
