<?php

namespace App\Filament\Resources\HashtagSets;

use App\Enums\ContentLanguage;
use App\Filament\Resources\HashtagSets\Pages\ManageHashtagSets;
use App\Models\HashtagSet;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class HashtagSetResource extends Resource
{
    protected static ?string $model = HashtagSet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static string|UnitEnum|null $navigationGroup = 'Social';

    protected static ?int $navigationSort = 50;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Hashtag Sets';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            Select::make('language')
                ->options(ContentLanguage::class)
                ->default(ContentLanguage::Mixed->value)
                ->required(),
            TagsInput::make('tags')
                ->helperText('Type a tag and press Enter. The # is optional.')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->weight('bold'),
                TextColumn::make('language')->badge(),
                TextColumn::make('tags')
                    ->badge()
                    ->limitList(6)
                    ->state(fn (HashtagSet $record) => collect($record->tags ?? [])
                        ->map(fn ($t) => str_starts_with($t, '#') ? $t : '#'.$t)
                        ->all()),
            ])
            ->filters([
                SelectFilter::make('language')->options(ContentLanguage::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageHashtagSets::route('/'),
        ];
    }
}
