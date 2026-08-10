<?php

namespace App\Filament\Resources\Ingredients\Schemas;

use App\Enums\HalalStatus;
use App\Enums\IngredientOrigin;
use App\Enums\PostStatus;
use App\Filament\Schemas\SeoMetaSchema;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class IngredientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->tabs([

                Tabs\Tab::make('Identity')->icon('heroicon-o-beaker')->schema([
                    Grid::make(['default' => 1, 'lg' => 3])->schema([
                        Section::make()->columnSpan(['lg' => 2])->schema([
                            TextInput::make('name')
                                ->required()->maxLength(180)
                                ->helperText('Common name, e.g. "Carmine".')
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, ?string $state, Set $set) => $operation === 'create'
                                    ? $set('slug', Str::slug((string) $state))
                                    : null),

                            TextInput::make('slug')
                                ->required()->maxLength(200)
                                ->unique(ignoreRecord: true)
                                ->disabledOn('edit')
                                ->dehydrated()
                                ->helperText('Drives /halal-ingredients/{slug}.'),

                            Grid::make(3)->schema([
                                TextInput::make('inci_name')->label('INCI name')->maxLength(200)
                                    ->helperText('The label-accurate identifier, e.g. "CI 75470".'),
                                TextInput::make('cas_number')->label('CAS number')->maxLength(40),
                                TextInput::make('ec_number')->label('EC number')->maxLength(40),
                            ]),

                            TagsInput::make('aliases')
                                ->helperText('Other names customers may search for, e.g. Cochineal, Natural Red 4.'),

                            Textarea::make('description')->rows(3)
                                ->helperText('Short summary. Feeds ingredient cards and the meta description fallback.'),

                            Textarea::make('benefits')->rows(3),
                        ]),

                        Section::make('Classification')->columnSpan(['lg' => 1])->schema([
                            Select::make('origin')->options(IngredientOrigin::class)->native(false),
                            TextInput::make('function')->maxLength(80)
                                ->helperText('emollient | surfactant | colourant | preservative | humectant'),
                            Toggle::make('is_animal_derived')->label('Animal derived'),
                            Toggle::make('is_alcohol')
                                ->label('Intoxicating alcohol')
                                ->helperText('Fatty alcohols (cetyl, cetearyl) are NOT flagged here.'),
                            Toggle::make('is_common_allergen'),
                            Toggle::make('is_key_ingredient_candidate')
                                ->label('Marketing-worthy active')
                                ->helperText('Surfaced as a key ingredient on product pages.'),
                        ]),
                    ]),
                ]),

                Tabs\Tab::make('Halal ruling')->icon('heroicon-o-shield-check')->schema([
                    Section::make()->schema([
                        Select::make('halal_status')
                            ->options(HalalStatus::class)
                            ->default(HalalStatus::Unknown)
                            ->required()
                            ->native(false)
                            ->helperText('Use "Depends on source" for glycerin, stearic acid, collagen and anything else whose ruling turns on feedstock.'),

                        Textarea::make('halal_notes')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('Why. e.g. "Glycerin may be plant- or tallow-derived; source verification required."'),

                        TextInput::make('verdict_summary')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->helperText('The one-line answer rendered above the fold: "Carmine is not halal — it is derived from insects."'),

                        Grid::make(2)->schema([
                            Select::make('reviewed_by_user_id')
                                ->label('Ruling signed off by')
                                ->relationship('reviewer', 'name')
                                ->searchable()->preload()->native(false)
                                ->helperText('These pages make religious rulings — record who approved the wording.'),
                            DateTimePicker::make('reviewed_at')->label('Signed off at')->seconds(false),
                        ]),
                    ]),
                ]),

                Tabs\Tab::make('Glossary page')->icon('heroicon-o-document-text')->schema([
                    Section::make()->schema([
                        Toggle::make('has_glossary_page')
                            ->label('Publish a page at /halal-ingredients/{slug}')
                            ->live()
                            ->helperText('Gates sitemap inclusion. Publish only the entries that answer a real question — a thin page per INCI entry is the doorway-page pattern.'),

                        RichEditor::make('content')
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'link'],
                                ['h2', 'h3'],
                                ['blockquote', 'bulletList', 'orderedList'],
                                ['attachFiles'],
                                ['undo', 'redo'],
                            ])
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('ingredients/content')
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            FileUpload::make('hero_image_path')
                                ->label('Hero image')
                                ->image()->imageEditor()
                                ->disk('public')->directory('ingredients')->visibility('public')
                                ->maxSize(4096)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                            TextInput::make('hero_image_alt')->label('Hero image alt text')->maxLength(255),
                        ]),

                        Grid::make(3)->schema([
                            Select::make('status')
                                ->options(PostStatus::class)
                                ->default(PostStatus::Draft)
                                ->required()->native(false),
                            DateTimePicker::make('published_at')->seconds(false),
                            Select::make('author_id')
                                ->label('Author')
                                ->relationship('author', 'name')
                                ->searchable()->preload()->native(false),
                        ]),

                        TextInput::make('reading_time_minutes')->numeric()->minValue(0),
                    ]),
                ]),

                Tabs\Tab::make('SEO')->icon('heroicon-o-magnifying-glass')->schema([
                    Section::make()->relationship('seoMeta')->schema(SeoMetaSchema::components()),
                ]),
            ]),
        ]);
    }
}
