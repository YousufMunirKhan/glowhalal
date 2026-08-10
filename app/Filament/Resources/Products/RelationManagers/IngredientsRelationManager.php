<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\HalalStatus;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * `position` reproduces the legally mandated INCI declaration order from the
 * physical packaging. Never re-sort this alphabetically.
 */
class IngredientsRelationManager extends RelationManager
{
    protected static string $relationship = 'ingredients';

    protected static ?string $title = 'Ingredients (INCI order)';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema->components(self::pivotFields());
    }

    /** Pivot fields, shared by the attach modal and the edit modal. */
    public static function pivotFields(): array
    {
        return [
            TextInput::make('position')
                ->label('INCI position')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->required()
                ->helperText('Declaration order from the pack — descending concentration.'),

            TextInput::make('concentration_percent')
                ->label('Concentration %')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->step('0.01'),

            Toggle::make('is_key_ingredient')
                ->label('Key ingredient')
                ->helperText('Featured on the product page.'),

            TextInput::make('source_note')
                ->maxLength(255)
                ->helperText('Resolves a "depends on source" ingredient, e.g. "palm-derived glycerin".'),

            Select::make('resolved_halal_status')
                ->label('Resolved halal status')
                ->options(HalalStatus::class)
                ->native(false)
                ->helperText('Per-product override once the actual source is verified.'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('pivot.position')->label('#')->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('inci_name')->label('INCI')->searchable()->placeholder('—'),
                TextColumn::make('halal_status')->label('Default status')->badge(),
                TextColumn::make('pivot.resolved_halal_status')
                    ->label('Resolved')
                    ->badge()
                    ->placeholder('—')
                    ->formatStateUsing(fn ($state) => $state instanceof HalalStatus
                        ? $state->getLabel()
                        : (HalalStatus::tryFrom((string) $state)?->getLabel() ?? $state))
                    ->color(fn ($state) => HalalStatus::tryFrom((string) ($state instanceof HalalStatus ? $state->value : $state))?->getColor() ?? 'gray'),
                TextColumn::make('pivot.source_note')->label('Source note')->wrap()->limit(50)->placeholder('—'),
                IconColumn::make('pivot.is_key_ingredient')->label('Key')->boolean(),
            ])
            ->defaultSort('ingredient_product.position')
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'inci_name'])
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        ...self::pivotFields(),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ]);
    }
}
