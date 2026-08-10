<?php

namespace App\Filament\Resources\ShippingZones\RelationManagers;

use App\Enums\ShippingRateType;
use App\Filament\Forms\MoneyInput;
use App\Models\ShippingRate;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RatesRelationManager extends RelationManager
{
    protected static string $relationship = 'rates';

    protected static ?string $title = 'Rates';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),

            Select::make('type')
                ->options(ShippingRateType::class)
                ->default(ShippingRateType::Flat)
                ->required()->native(false),

            Grid::make(2)->schema([
                MoneyInput::make('amount', 'Rate')->required()->default(0),
                MoneyInput::make('free_over_subtotal_amount', 'Free over')
                    ->helperText('Subtotal at which this rate becomes free.'),
                MoneyInput::make('cod_surcharge_amount', 'COD surcharge'),
            ]),

            Grid::make(2)->schema([
                TextInput::make('min_weight_grams')->numeric()->minValue(0),
                TextInput::make('max_weight_grams')->numeric()->minValue(0),
                TextInput::make('min_delivery_days')->numeric()->minValue(0),
                TextInput::make('max_delivery_days')->numeric()->minValue(0),
            ]),

            Grid::make(2)->schema([
                TextInput::make('position')->numeric()->minValue(0)->default(0),
                Toggle::make('is_active')->default(true),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('amount')->label('Rate')
                    ->formatStateUsing(fn ($state) => MoneyInput::format($state)),
                TextColumn::make('free_over_subtotal_amount')->label('Free over')
                    ->formatStateUsing(fn ($state) => MoneyInput::format($state)),
                TextColumn::make('delivery')
                    ->label('Delivery')
                    ->state(fn (ShippingRate $record) => $record->deliveryEstimate() ?? '—'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('position')
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
