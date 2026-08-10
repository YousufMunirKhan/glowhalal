<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BlogPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(50)
                    ->wrap()
                    ->weight('medium'),
                // The category NAME, not the raw id.
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->toggleable(),
                // Which language version this post is (EN vs Roman Urdu mirror).
                TextColumn::make('locale')
                    ->label('Language')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state === 'ur-Latn' ? 'Roman Urdu' : 'English')
                    ->toggleable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('j M Y')
                    ->sortable()
                    ->placeholder('Not published'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
