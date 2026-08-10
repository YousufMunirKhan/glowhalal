<?php

namespace App\Filament\Resources\Ingredients\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * "See also" cluster links. A 20-page cluster with no internal links is
 * 20 orphans; cross-linked by relevance it ranks as a cluster. §2.7.
 */
class RelatedRelationManager extends RelationManager
{
    protected static string $relationship = 'related';

    protected static ?string $title = 'See also';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('position')->numeric()->minValue(0)->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('pivot.position')->label('#')->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('inci_name')->label('INCI')->placeholder('—'),
                TextColumn::make('halal_status')->badge(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'inci_name'])
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('position')->numeric()->minValue(0)->default(0),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ]);
    }
}
