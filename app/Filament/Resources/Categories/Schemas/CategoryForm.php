<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([

                Section::make()->columnSpan(['lg' => 2])->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(150)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $operation, ?string $state, Set $set) => $operation === 'create'
                            ? $set('slug', Str::slug((string) $state))
                            : null),

                    TextInput::make('slug')
                        ->required()
                        ->maxLength(180)
                        ->unique(ignoreRecord: true)
                        ->helperText('Changing this on a published category creates a 301 redirect from the old URL.')
                        ->disabledOn('edit')
                        ->dehydrated(),

                    Textarea::make('description')
                        ->rows(4)
                        ->columnSpanFull(),

                    FileUpload::make('image_path')
                        ->label('Category image')
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatioOptions(['1:1', '4:5', '16:9'])
                        ->disk('public')
                        ->directory('categories')
                        ->visibility('public')
                        ->maxSize(4096)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),

                    TextInput::make('image_alt')
                        ->label('Image alt text')
                        ->maxLength(255)
                        ->helperText('Describe the image for screen readers and search engines.'),
                ]),

                Section::make('Placement')->columnSpan(['lg' => 1])->schema([
                    Select::make('parent_id')
                        ->label('Parent category')
                        ->options(fn (?Category $record) => Category::query()
                            ->when($record?->exists, fn ($q) => $q->whereKeyNot($record->getKey()))
                            ->orderBy('path')
                            ->orderBy('position')
                            ->get()
                            ->mapWithKeys(fn (Category $c) => [$c->id => $c->indentedName()])
                            ->all())
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->placeholder('Top level')
                        ->helperText('Leave empty for a top-level category. Path and depth are maintained automatically.'),

                    TextInput::make('position')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Lower numbers sort first among siblings.'),

                    Toggle::make('is_active')->default(true),
                    Toggle::make('show_in_menu')->default(true),
                    Toggle::make('is_featured'),
                ]),
            ]),
        ]);
    }
}
