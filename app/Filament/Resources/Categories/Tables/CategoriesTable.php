<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('parent:id,name'))
            ->columns([
                ImageColumn::make('image_path')
                    ->label('')
                    ->disk('public')
                    ->height(40)
                    ->width(40)
                    ->extraImgAttributes(['class' => 'rounded object-cover']),

                // Indented by depth so the tree structure is readable in a flat table.
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (Category $record, $state) => str_repeat('— ', (int) $record->depth).$state)
                    ->description(fn (Category $record) => $record->slug),

                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('Top level')
                    ->toggleable(),

                TextColumn::make('depth')
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('position')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_active')->label('Active')->boolean(),
                IconColumn::make('show_in_menu')->label('In menu')->boolean()->toggleable(),
                IconColumn::make('is_featured')->label('Featured')->boolean()->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->label('Parent')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active'),
                TernaryFilter::make('show_in_menu')->label('Shown in menu'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    // CategoryObserver throws on deleting a category with children; catch it
                    // here so the admin gets a notification instead of an exception page.
                    DeleteAction::make()
                        ->before(function (Category $record, DeleteAction $action) {
                            if ($record->children()->exists()) {
                                Notification::make()
                                    ->danger()
                                    ->title('This category has sub-categories')
                                    ->body('Re-parent or delete the '.$record->children()->count().' child categories first.')
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            // Materialised path ordering renders parents immediately above their children.
            ->defaultSort(fn (Builder $query) => $query->orderByRaw('COALESCE(path, "") ASC')->orderBy('position'));
    }
}
