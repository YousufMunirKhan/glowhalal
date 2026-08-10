<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Images';

    protected static ?string $recordTitleAttribute = 'alt_text';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('path')
                ->label('Image')
                ->image()
                ->imageEditor()
                ->imageEditorAspectRatioOptions(['1:1', '4:5', '16:9'])
                ->disk('public')
                ->directory('products')
                ->visibility('public')
                ->maxSize(4096)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->required()
                ->columnSpanFull(),

            // NOT NULL in the schema, and required here on purpose: missing alt text
            // is both an accessibility failure and forfeited image-search traffic.
            TextInput::make('alt_text')
                ->required()
                ->maxLength(255)
                ->helperText('Describe the image for screen readers and search engines. Include the shade name.')
                ->columnSpanFull(),

            Select::make('product_variant_id')
                ->label('Specific to variant')
                ->relationship(
                    name: 'variant',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn ($query) => $query->where('product_id', $this->getOwnerRecord()->getKey()),
                )
                ->searchable()
                ->preload()
                ->native(false)
                ->placeholder('Applies to all variants'),

            TextInput::make('position')->numeric()->minValue(0)->default(0),

            Toggle::make('is_primary')
                ->helperText('The image used on listing cards and social shares.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text')
            ->columns([
                ImageColumn::make('path')->label('Image')->disk('public')
                    ->height(56)->width(56)->extraImgAttributes(['class' => 'rounded object-cover']),
                TextColumn::make('alt_text')->label('Alt text')->wrap()->limit(80)->searchable(),
                TextColumn::make('variant.name')->label('Variant')->placeholder('All variants'),
                TextColumn::make('position')->sortable(),
                IconColumn::make('is_primary')->label('Primary')->boolean(),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->headerActions([
                CreateAction::make()->mutateDataUsing(function (array $data): array {
                    $data['disk'] ??= 'public';

                    return $data;
                }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
