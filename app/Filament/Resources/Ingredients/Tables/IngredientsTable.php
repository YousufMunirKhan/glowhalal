<?php

namespace App\Filament\Resources\Ingredients\Tables;

use App\Enums\HalalStatus;
use App\Enums\IngredientOrigin;
use App\Enums\PostStatus;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IngredientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()
                    ->description(fn ($record) => $record->inci_name),

                TextColumn::make('halal_status')->label('Halal status')->badge()->sortable(),

                TextColumn::make('origin')->badge()->placeholder('—')->toggleable(),

                TextColumn::make('function')->placeholder('—')->toggleable(),

                IconColumn::make('is_animal_derived')->label('Animal')->boolean()
                    ->trueColor('warning')->falseColor('gray'),

                IconColumn::make('is_alcohol')->label('Alcohol')->boolean()
                    ->trueColor('danger')->falseColor('gray'),

                IconColumn::make('has_glossary_page')->label('Has page')->boolean(),

                TextColumn::make('status')->badge()->toggleable(),

                TextColumn::make('products_count')->label('Products')
                    ->counts('products')->badge()->color('gray'),

                TextColumn::make('reviewed_at')->label('Signed off')
                    ->date('d M Y')->placeholder('Not reviewed')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('halal_status')->options(HalalStatus::class),
                SelectFilter::make('origin')->options(IngredientOrigin::class),
                SelectFilter::make('status')->options(PostStatus::class),
                TernaryFilter::make('has_glossary_page')->label('Has glossary page'),
                TernaryFilter::make('is_animal_derived'),
                Filter::make('needs_review')
                    ->label('Needs source review')
                    ->query(fn (Builder $q) => $q->whereIn('halal_status', [
                        HalalStatus::Mashbooh,
                        HalalStatus::DependsOnSource,
                        HalalStatus::Unknown,
                    ])),
                Filter::make('unsigned')
                    ->label('Ruling not signed off')
                    ->query(fn (Builder $q) => $q->whereNull('reviewed_at')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([EditAction::make(), DeleteAction::make()]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
