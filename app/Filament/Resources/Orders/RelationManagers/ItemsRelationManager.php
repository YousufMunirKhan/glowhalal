<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Support\Money;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Read-only. Order lines are a commercial record — every value here is a
 * snapshot taken at placement and must not drift with the live catalogue.
 */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Line items';

    protected static ?string $recordTitleAttribute = 'product_name';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                ImageColumn::make('image_path_snapshot')->label('')->disk('public')
                    ->height(40)->width(40)->extraImgAttributes(['class' => 'rounded object-cover']),
                TextColumn::make('product_name')->label('Product')->wrap()
                    ->description(fn ($record) => $record->variant_name),
                TextColumn::make('sku')->label('SKU')->copyable(),
                TextColumn::make('quantity')->label('Qty'),
                TextColumn::make('unit_price_amount')->label('Unit price')
                    ->formatStateUsing(fn ($state) => $state instanceof Money ? $state->format() : '—'),
                TextColumn::make('line_total_amount')->label('Line total')
                    ->formatStateUsing(fn ($state) => $state instanceof Money ? $state->format() : '—'),
                TextColumn::make('halal_snapshot.overall_status')
                    ->label('Halal at purchase')
                    ->badge()
                    ->placeholder('—'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
