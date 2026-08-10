<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\AlcoholStatus;
use App\Enums\HalalStatus;
use App\Enums\ProductStatus;
use App\Filament\Forms\MoneyInput;
use App\Filament\Schemas\SeoMetaSchema;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->tabs([

                Tabs\Tab::make('Details')->icon('heroicon-o-information-circle')->schema([
                    Grid::make(['default' => 1, 'lg' => 3])->schema([
                        Section::make()->columnSpan(['lg' => 2])->schema([
                            TextInput::make('name')
                                ->required()->maxLength(200)
                                ->live(onBlur: true)
                                // Fill the slug from the name on a NEW product, or if the
                                // slug is still empty. Never silently rewrite the slug of an
                                // already-published product (that would break its URL).
                                ->afterStateUpdated(function (string $operation, ?string $state, Get $get, Set $set) {
                                    if ($operation === 'create' || blank($get('slug'))) {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),

                            TextInput::make('slug')
                                ->required()->maxLength(220)
                                ->unique(ignoreRecord: true)
                                ->helperText('Changing this on a published product creates a 301 redirect from the old URL.')
                                ->disabledOn('edit')      // deliberate: see §7.2
                                ->dehydrated(),

                            Textarea::make('short_description')->rows(3)->maxLength(500)
                                ->helperText('Plain text. Used on listing cards and as the meta description fallback.'),

                            RichEditor::make('description')
                                ->toolbarButtons([
                                    ['bold', 'italic', 'underline', 'link'],
                                    ['h2', 'h3'],
                                    ['blockquote', 'bulletList', 'orderedList'],
                                    ['attachFiles'],
                                    ['undo', 'redo'],
                                ])
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('products/content')
                                ->columnSpanFull(),

                            RichEditor::make('how_to_use')
                                ->toolbarButtons([['bold', 'italic'], ['bulletList', 'orderedList']])
                                ->columnSpanFull(),

                            Textarea::make('ingredients_note')
                                ->label('Full INCI list (as printed on the pack)')
                                ->rows(4)
                                ->columnSpanFull()
                                ->helperText('Verbatim from the packaging. The structured list lives in the Ingredients relation below.'),
                        ]),

                        Section::make('Publishing')->columnSpan(['lg' => 1])->schema([
                            Select::make('status')->options(ProductStatus::class)
                                ->default(ProductStatus::Draft)->required()->native(false),

                            DateTimePicker::make('published_at')->seconds(false),

                            Select::make('primary_category_id')
                                ->label('Primary category')
                                ->relationship('primaryCategory', 'name')
                                ->searchable()->preload()->native(false)
                                ->helperText('Drives breadcrumbs and the canonical category.'),

                            Select::make('categories')
                                ->relationship('categories', 'name')
                                ->multiple()->searchable()->preload(),

                            TextInput::make('brand')->maxLength(100),

                            TextInput::make('sku_prefix')
                                ->maxLength(20)
                                ->helperText('Prefixes generated variant SKUs, e.g. GH-LIP.'),

                            Toggle::make('is_featured'),
                            Toggle::make('is_new_arrival'),
                        ]),
                    ]),

                    // ── Simple pricing & stock ───────────────────────────────
                    // For a one-price / one-stock product the owner never has to
                    // open the Variants tab. These two fields are virtual: the
                    // Create/Edit pages project them onto a single default variant
                    // and its inventory row (see HandlesSimpleProductPricing).
                    Section::make('Pricing & stock')
                        ->icon('heroicon-o-banknotes')
                        ->description('One price, one stock number. This is all most products need.')
                        ->columnSpanFull()
                        ->visible(fn (?Product $record) => ! self::isVariantProduct($record))
                        ->schema([
                            Grid::make(2)->schema([
                                // Same Rs-prefix / integer-paisa handling as the variant form.
                                MoneyInput::make('simple_price', 'Price (Rs)')
                                    ->helperText('The selling price shown to customers. Set this to make the product purchasable.'),

                                TextInput::make('simple_stock')
                                    ->label('Stock quantity')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->helperText('How many units you have to sell. Whole numbers.'),
                            ]),
                        ]),

                    // A product that genuinely has several variants is left to the
                    // Variants tab — we don't fight the owner with simple fields.
                    Section::make('Pricing & stock')
                        ->icon('heroicon-o-squares-2x2')
                        ->columnSpanFull()
                        ->visible(fn (?Product $record) => self::isVariantProduct($record))
                        ->schema([
                            Placeholder::make('variant_notice')
                                ->hiddenLabel()
                                ->content('This product uses variants — manage each option\'s price and stock in the Variants tab below.'),
                        ]),

                    // ── Prominent image uploader ─────────────────────────────
                    // Brought up from the old Images relation-manager tab (which
                    // owners could not find) into the main Details tab. Reuses the
                    // relation-manager's FileUpload settings.
                    Repeater::make('images')
                        ->relationship()
                        ->label('Product images')
                        ->helperText('Add one or more photos. The first (or the one marked primary) is used on listing cards and social shares. Drag to reorder.')
                        ->columnSpanFull()
                        ->reorderable()
                        ->orderColumn('position')
                        ->collapsible()
                        ->defaultItems(0)
                        ->addActionLabel('Add image')
                        ->itemLabel(fn (array $state): ?string => $state['alt_text'] ?? null)
                        ->schema([
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

                            // NOT NULL in the schema, and required here on purpose:
                            // missing alt text is both an accessibility failure and
                            // forfeited image-search traffic.
                            TextInput::make('alt_text')
                                ->label('Alt text (describe the photo)')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Describe the image for screen readers and search engines, e.g. "50 ml amber oil bottle on a white background".')
                                ->columnSpanFull(),

                            Toggle::make('is_primary')
                                ->helperText('The main image, used on listing cards and social shares. Mark just one.'),
                        ]),

                    // ── FAQ editor ───────────────────────────────────────────
                    // Stored on products.faqs (array cast) with keys q/a — exactly
                    // what the product page renders and JsonLd::faqPage reads.
                    Repeater::make('faqs')
                        ->label('Frequently asked questions')
                        ->helperText('Shown on the product page and used for FAQ rich results. Great for common buyer questions.')
                        ->columnSpanFull()
                        ->collapsible()
                        ->reorderable()
                        ->defaultItems(0)
                        ->addActionLabel('Add question')
                        ->itemLabel(fn (array $state): ?string => $state['q'] ?? null)
                        ->default([])
                        ->schema([
                            TextInput::make('q')
                                ->label('Question')
                                ->required()
                                ->maxLength(300)
                                ->columnSpanFull(),

                            Textarea::make('a')
                                ->label('Answer')
                                ->required()
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                ]),

                Tabs\Tab::make('Halal')->icon('heroicon-o-shield-check')->schema([
                    Section::make('Halal profile')
                        ->relationship('halalProfile')     // 1:1 — Filament creates the row on save
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('overall_status')->options(HalalStatus::class)->required()->native(false)
                                    ->default(HalalStatus::Unknown),
                                Select::make('alcohol_status')->options(AlcoholStatus::class)->required()->native(false)
                                    ->default(AlcoholStatus::None)
                                    ->helperText('Fatty alcohols (cetyl, cetearyl) are not intoxicating — use "Fatty alcohol only".'),
                                Toggle::make('is_wudu_friendly')->helperText('Water-permeable; permits ablution.'),
                                Toggle::make('is_vegan'),
                                Toggle::make('is_cruelty_free'),
                                Toggle::make('is_self_declared')
                                    ->helperText('Manufacturer statement without third-party certification. Rendered as the weaker claim.'),
                                Toggle::make('shared_facility_warning'),
                            ]),
                            Grid::make(2)->schema([
                                TextInput::make('manufacturer_name')->maxLength(180),
                                TextInput::make('manufacturing_country')->maxLength(2)
                                    ->helperText('Two-letter ISO code, e.g. PK.'),
                            ]),
                            Textarea::make('summary')->rows(3)->columnSpanFull()
                                ->helperText('Shown on the product page under the halal badge.'),
                            Textarea::make('internal_notes')->rows(2)->columnSpanFull(),
                        ]),

                    Section::make('"Free from" claims')->schema([
                        Select::make('freeFromAttributes')
                            ->label('Claims')
                            ->relationship('freeFromAttributes', 'name')
                            ->multiple()->preload()
                            ->helperText('Claims only appear on the storefront once verified.'),
                    ]),
                ]),

                Tabs\Tab::make('SEO')->icon('heroicon-o-magnifying-glass')->schema([
                    Section::make()->relationship('seoMeta')->schema(SeoMetaSchema::components()),
                ]),
            ]),
        ]);
    }

    /** True only for a real variant product (more than one active variant). */
    private static function isVariantProduct(?Product $record): bool
    {
        return $record !== null
            && $record->variants()->where('is_active', true)->count() > 1;
    }
}
