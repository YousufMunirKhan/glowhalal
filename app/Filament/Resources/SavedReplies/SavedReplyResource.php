<?php

namespace App\Filament\Resources\SavedReplies;

use App\Enums\ContentLanguage;
use App\Enums\SavedReplyCategory;
use App\Filament\Resources\SavedReplies\Pages\ManageSavedReplies;
use App\Models\SavedReply;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class SavedReplyResource extends Resource
{
    protected static ?string $model = SavedReply::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Social';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Saved Replies';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Select::make('category')
                ->options(SavedReplyCategory::class)
                ->required(),
            Select::make('language')
                ->options(ContentLanguage::class)
                ->default(ContentLanguage::RomanUrdu->value)
                ->required(),
            Textarea::make('body')
                ->label('Reply text')
                ->rows(5)
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->weight('bold'),
                TextColumn::make('category')->badge()->sortable(),
                TextColumn::make('language')->badge()->sortable(),
                TextColumn::make('body')
                    ->label('Reply')
                    ->limit(60)
                    ->copyable()
                    ->copyMessage('Reply copied')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('category')->options(SavedReplyCategory::class),
                SelectFilter::make('language')->options(ContentLanguage::class),
            ])
            ->recordActions([
                Action::make('copy')
                    ->label('Copy')
                    ->icon(Heroicon::OutlinedClipboard)
                    ->color('gray')
                    ->action(fn () => null)
                    ->extraAttributes(fn (SavedReply $record) => [
                        'x-on:click' => 'navigator.clipboard.writeText('.json_encode($record->body).')',
                    ]),
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
            'index' => ManageSavedReplies::route('/'),
        ];
    }
}
