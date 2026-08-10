<?php

namespace App\Filament\Pages;

use App\Settings\StoreSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageStoreSettings extends SettingsPage
{
    protected static string $settings = StoreSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Store settings';

    protected static ?string $navigationLabel = 'Store settings';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->tabs([

                Tabs\Tab::make('Contact')->icon('heroicon-o-phone')->schema([
                    Section::make()
                        ->description('These appear in the site footer, on the contact page, and in order emails. Leave anything blank that you do not want shown.')
                        ->schema([
                            Grid::make(['default' => 1, 'md' => 2])->schema([
                                TextInput::make('contact_email')
                                    ->label('Contact email')
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('contact_phone')
                                    ->label('Phone number')
                                    ->tel()
                                    ->maxLength(40)
                                    ->helperText('Include the country code, e.g. +92 300 1234567.'),

                                TextInput::make('whatsapp_number')
                                    ->label('WhatsApp number')
                                    ->tel()
                                    ->maxLength(40)
                                    ->helperText('Digits only is fine — the wa.me link is built automatically.'),

                                TextInput::make('opening_hours')
                                    ->label('Opening hours')
                                    ->maxLength(120)
                                    ->helperText('Free text, e.g. "Mon–Sat, 10am–7pm".'),
                            ]),

                            Textarea::make('address_line')
                                ->label('Street address')
                                ->rows(2)
                                ->columnSpanFull(),

                            Grid::make(['default' => 1, 'md' => 2])->schema([
                                TextInput::make('city')->maxLength(80),
                                TextInput::make('postal_code')->label('Postal code')->maxLength(20),
                            ]),
                        ]),
                ]),

                Tabs\Tab::make('Brand & founder')->icon('heroicon-o-user-circle')->schema([
                    Section::make('Founder')
                        ->description('Shown on the About page. Leave blank until you have the real wording and photo — the storefront hides the whole block when the name or bio is empty.')
                        ->schema([
                            Grid::make(['default' => 1, 'md' => 2])->schema([
                                TextInput::make('founder_name')->label('Founder name')->maxLength(120),
                                TextInput::make('founder_title')
                                    ->label('Role / title')
                                    ->maxLength(120)
                                    ->helperText('e.g. "Founder" or "Founder & Formulator".'),
                            ]),

                            Textarea::make('founder_bio')
                                ->label('Founder bio')
                                ->rows(6)
                                ->columnSpanFull()
                                ->helperText('Written in the founder\'s own words. This is the paragraph customers read to decide whether to trust the brand.'),

                            FileUpload::make('founder_photo_path')
                                ->label('Founder photo')
                                ->image()
                                ->imageEditor()
                                ->imageEditorAspectRatioOptions(['1:1', '4:5'])
                                ->disk('public')
                                ->directory('brand')
                                ->visibility('public')
                                ->maxSize(4096)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                        ]),

                    Section::make('Brand story')->schema([
                        RichEditor::make('brand_story')
                            ->hiddenLabel()
                            ->toolbarButtons([
                                ['bold', 'italic', 'link'],
                                ['h2', 'h3'],
                                ['blockquote', 'bulletList', 'orderedList'],
                                ['undo', 'redo'],
                            ])
                            ->columnSpanFull(),
                    ]),
                ]),

                Tabs\Tab::make('Storefront copy')->icon('heroicon-o-megaphone')->schema([
                    Section::make('Announcement bar')
                        ->description('The strip across the top of every page. It stays hidden until you switch it on AND enter text.')
                        ->schema([
                            Toggle::make('announcement_enabled')->label('Show the announcement bar'),
                            TextInput::make('announcement_text')->label('Announcement text')->maxLength(180),
                            TextInput::make('announcement_url')
                                ->label('Link (optional)')
                                ->url()
                                ->maxLength(255),
                        ]),

                    Section::make('Homepage hero')->schema([
                        TextInput::make('hero_heading')->label('Heading')->maxLength(140),
                        Textarea::make('hero_subheading')->label('Sub-heading')->rows(3),
                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            TextInput::make('hero_cta_label')->label('Button label')->maxLength(60),
                            TextInput::make('hero_cta_url')->label('Button link')->maxLength(255),
                        ]),
                    ]),
                ]),

                Tabs\Tab::make('Delivery')->icon('heroicon-o-truck')->schema([
                    Section::make()->schema([
                        TagsInput::make('couriers')
                            ->label('Couriers you use')
                            ->placeholder('Add a courier')
                            ->helperText('Press Enter after each name. These are the options offered when marking an order shipped.'),

                        Repeater::make('delivery_estimates')
                            ->label('Delivery estimates')
                            ->schema([
                                Grid::make(['default' => 1, 'md' => 2])->schema([
                                    TextInput::make('zone')
                                        ->label('Area')
                                        ->required()
                                        ->maxLength(120)
                                        ->helperText('e.g. "Karachi" or "Rest of Pakistan".'),
                                    TextInput::make('estimate')
                                        ->label('Estimate')
                                        ->required()
                                        ->maxLength(120)
                                        ->helperText('e.g. "2–4 working days".'),
                                ]),
                            ])
                            ->addActionLabel('Add an area')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['zone'] ?? null)
                            ->columnSpanFull(),

                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            TextInput::make('free_delivery_threshold_amount')
                                ->label('Free delivery over')
                                ->prefix('Rs')
                                ->inputMode('decimal')
                                ->step('any')
                                ->rule('numeric')
                                ->rule('min:0')
                                ->helperText('Leave blank if you do not offer free delivery.')
                                // Stored as integer paisa; typed in rupees.
                                ->formatStateUsing(fn ($state) => $state === null ? null : ((float) $state) / 100)
                                ->dehydrateStateUsing(fn ($state) => $state === null || $state === ''
                                    ? null
                                    : (int) round(((float) $state) * 100)),

                            TextInput::make('cod_fee_amount')
                                ->label('Cash-on-delivery fee')
                                ->prefix('Rs')
                                ->inputMode('decimal')
                                ->step('any')
                                ->rule('numeric')
                                ->rule('min:0')
                                ->helperText('Leave blank for no COD surcharge.')
                                ->formatStateUsing(fn ($state) => $state === null ? null : ((float) $state) / 100)
                                ->dehydrateStateUsing(fn ($state) => $state === null || $state === ''
                                    ? null
                                    : (int) round(((float) $state) * 100)),
                        ]),

                        Toggle::make('cod_enabled')
                            ->label('Offer cash on delivery')
                            ->helperText('Turning this off removes COD from checkout entirely.'),

                        Textarea::make('delivery_note')
                            ->label('Delivery note')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Shown on the delivery information page and at checkout.'),

                        Textarea::make('returns_policy_summary')
                            ->label('Returns policy summary')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                ]),

                Tabs\Tab::make('Social')->icon('heroicon-o-share')->schema([
                    Section::make()
                        ->description('Full URLs. Anything left blank simply does not appear in the footer.')
                        ->schema([
                            Grid::make(['default' => 1, 'md' => 2])->schema([
                                TextInput::make('instagram_url')->label('Instagram')->url()->maxLength(255)
                                    ->prefixIcon('heroicon-o-link'),
                                TextInput::make('facebook_url')->label('Facebook')->url()->maxLength(255)
                                    ->prefixIcon('heroicon-o-link'),
                                TextInput::make('tiktok_url')->label('TikTok')->url()->maxLength(255)
                                    ->prefixIcon('heroicon-o-link'),
                                TextInput::make('youtube_url')->label('YouTube')->url()->maxLength(255)
                                    ->prefixIcon('heroicon-o-link'),
                            ]),

                            TextInput::make('whatsapp_url')
                                ->label('WhatsApp link override')
                                ->url()
                                ->maxLength(255)
                                ->helperText('Only needed if you want something other than the wa.me link built from the number on the Contact tab.'),
                        ]),
                ]),
            ]),
        ]);
    }
}
