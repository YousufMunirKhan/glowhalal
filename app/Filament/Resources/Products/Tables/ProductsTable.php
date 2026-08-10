<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\CertificationStatus;
use App\Enums\ProductStatus;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Not optional: without these the image, category and halal columns
            // each fire a query per row — 25 rows becomes ~75 queries.
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'primaryCategory:id,name', 'primaryImage', 'halalProfile',
            ]))
            ->columns([
                ImageColumn::make('primaryImage.path')->label('')->disk('public')
                    ->height(44)->width(44)->extraImgAttributes(['class' => 'rounded object-cover']),

                TextColumn::make('name')->searchable()->sortable()
                    ->description(fn ($record) => $record->primaryCategory?->name)
                    ->wrap()->limit(60),

                TextColumn::make('price_min_amount')->label('Price')->sortable()
                    ->formatStateUsing(fn ($record) => $record->price_range),

                TextColumn::make('total_stock')->label('Stock')->sortable()->badge()
                    ->color(fn ($state) => match (true) {
                        (int) $state === 0 => 'danger',
                        (int) $state <= 5 => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('status')->badge()->sortable(),

                IconColumn::make('halalProfile.is_certified')->label('Certified')
                    ->boolean()->trueIcon('heroicon-o-shield-check')->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')->falseColor('gray'),

                TextColumn::make('variants_count')->counts('variants')->label('Variants')->toggleable(),

                TextColumn::make('created_at')->dateTime('d M Y')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(ProductStatus::class),
                SelectFilter::make('primary_category_id')->relationship('primaryCategory', 'name')
                    ->label('Category')->searchable()->preload(),
                TernaryFilter::make('is_featured'),
                Filter::make('out_of_stock')->query(fn (Builder $q) => $q->where('total_stock', 0))->label('Out of stock'),
                Filter::make('uncertified')
                    ->label('Missing halal certification')
                    ->query(fn (Builder $q) => $q->whereDoesntHave('certifications',
                        fn (Builder $c) => $c->where('status', CertificationStatus::Active))),
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
