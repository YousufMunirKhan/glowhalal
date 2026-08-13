<?php

namespace App\Filament\Pages;

use App\Settings\SeoSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSeoSettings extends SettingsPage
{
    protected static string $settings = SeoSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'SEO & Integrations';

    protected static ?string $navigationLabel = 'SEO & Integrations';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Search engine visibility')
                ->description('The master switch for the whole site.')
                ->schema([
                    Toggle::make('is_indexable')
                        ->label('Allow search engines to index this site')
                        ->helperText('Leave this OFF while the site is being built. Turn it on at launch — and do not turn it off again afterwards, because pages drop out of Google far faster than they come back.'),
                ]),

            Section::make('Defaults')
                ->description('Used whenever a product, post or page has no SEO override of its own.')
                ->schema([
                    Grid::make(['default' => 1, 'md' => 2])->schema([
                        TextInput::make('site_name')->label('Site name')->maxLength(120),
                        TextInput::make('title_suffix')
                            ->label('Title suffix')
                            ->maxLength(60)
                            ->helperText('Appended to every page title, e.g. " | Glow Halal".'),
                    ]),

                    TextInput::make('default_meta_title')
                        ->label('Default meta title')
                        ->maxLength(255)
                        ->helperText('Aim for 50–60 characters.'),

                    Textarea::make('default_meta_description')
                        ->label('Default meta description')
                        ->rows(3)
                        ->maxLength(320)
                        ->helperText('Aim for 150–160 characters. This is the snippet under your link in search results.'),

                    FileUpload::make('default_og_image_path')
                        ->label('Default social share image')
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatioOptions(['16:9'])
                        ->disk('public')
                        ->directory('seo')
                        ->visibility('public')
                        ->maxSize(4096)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText('1200×630 renders best across platforms.'),
                ]),

            Section::make('Organisation')
                ->description('Feeds the Organization structured data block.')
                ->schema([
                    TextInput::make('organisation_name')->label('Organisation name')->maxLength(180),
                    FileUpload::make('organisation_logo_path')
                        ->label('Organisation logo')
                        ->image()
                        ->disk('public')
                        ->directory('seo')
                        ->visibility('public')
                        ->maxSize(2048)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']),
                ]),

            Section::make('Analytics & ad conversion tracking')
                ->description('Visitor and conversion tracking. Everything here only loads AFTER a visitor accepts cookies, and no personal data (name, phone, email, address) is ever sent. Leave a field blank until you actually have that account — a blank field fires nothing.')
                ->collapsed()
                ->schema([
                    Grid::make(['default' => 1, 'md' => 2])->schema([
                        TextInput::make('google_analytics_id')->label('Google Analytics ID')
                            ->maxLength(40)->placeholder('G-XXXXXXXXXX')
                            ->helperText('What it does: counts your visitors and records add-to-cart, checkout and purchase events (the sale Google Ads counts). Where to get it: analytics.google.com → Admin → Data streams → your stream → "Measurement ID" (starts with G-).'),
                        TextInput::make('google_tag_manager_id')->label('Google Tag Manager ID')
                            ->maxLength(40)->placeholder('GTM-XXXXXXX')
                            ->helperText('Optional. Only needed if you manage tags through Tag Manager instead of pasting the Analytics ID above.'),
                        TextInput::make('meta_pixel_id')->label('Meta (Facebook) Pixel ID')->maxLength(40)
                            ->placeholder('1234567890')
                            ->helperText('Optional. Paste ONLY your Pixel ID and Facebook/Instagram tracking (ViewContent, AddToCart, InitiateCheckout, Purchase) turns on automatically. Where to get it: business.facebook.com → Events Manager → your pixel → Settings. Leave blank if you do not run Meta ads.'),
                        TextInput::make('google_ads_id')->label('Google Ads tag ID')
                            ->maxLength(40)->placeholder('AW-123456789')
                            ->helperText('What it does: puts your Google tag on every page, which is what builds remarketing audiences and lets Google optimise your campaigns. Where to get it: ads.google.com → Tools → Data manager → Google tag (looks like AW-123456789, no slash). Leave blank if you do not run Google Ads.'),
                        TextInput::make('google_ads_conversion')->label('Google Ads conversion label')
                            ->maxLength(60)->placeholder('AW-123456789/AbCdEfGhIj')
                            ->helperText('Optional, and separate from the tag ID above — this one records the SALE. Where to get it: ads.google.com → Goals → Conversions → your "Purchase" action → "Tag setup" → the value shown as send_to. It must include the "/" and the label after it; the AW- number on its own cannot record a conversion.'),
                    ]),
                ]),

            Section::make('Site ownership verification (Google, Bing & Facebook)')
                ->description('These prove to Google, Bing and Meta that you own the site — needed to submit your sitemap, see your search traffic, and connect the product catalog to Facebook/Instagram. Each platform gives you a code — paste it here, then click "Verify" on their side.')
                ->collapsed()
                ->schema([
                    TextInput::make('google_site_verification')->label('Google site verification code')
                        ->maxLength(120)
                        ->helperText('Where to get it: search.google.com/search-console → add https://glowhalal.com → choose "HTML tag". Copy only the content="..." value and paste it here.'),
                    TextInput::make('bing_site_verification')->label('Bing site verification code (also verifies Yahoo)')
                        ->maxLength(120)
                        ->helperText('Yahoo runs on Bing, so this one code covers Bing + Yahoo + DuckDuckGo. Where to get it: bing.com/webmasters → add your site → "HTML Meta Tag". Copy only the content="..." value (the msvalidate.01 code) and paste it here.'),
                    TextInput::make('facebook_domain_verification')->label('Facebook (Meta) domain verification code')
                        ->maxLength(120)
                        ->helperText('Proves to Meta that you own glowhalal.com — required for the product catalog and ad domain claims. Where to get it: business.facebook.com → Settings → Brand Safety → Domains → "Meta-tag verification". Copy only the content="..." value and paste it here.'),
                ]),

            Section::make('Sign in with Google (customer login)')
                ->description('Lets customers log in with their Google account and powers the one-tap prompt. These come from your Google Cloud project — they are pre-filled with the current values; only change them if you create a new OAuth client.')
                ->collapsed()
                ->schema([
                    TextInput::make('google_oauth_client_id')->label('Google OAuth Client ID')
                        ->maxLength(255)
                        ->helperText('Where to get it: console.cloud.google.com → APIs & Services → Credentials → your OAuth 2.0 Client ID. Ends in .apps.googleusercontent.com. Safe to be public.'),
                    TextInput::make('google_oauth_client_secret')->label('Google OAuth Client Secret')
                        ->password()->revealable()
                        ->maxLength(255)
                        ->helperText('SECRET — keep it private. Same Credentials screen as the Client ID (starts with GOCSPX-). Never share it publicly.'),
                    TextInput::make('google_oauth_redirect')->label('Authorised redirect URI')
                        ->maxLength(255)
                        ->helperText('Must EXACTLY match the "Authorised redirect URI" in your Google OAuth client. For this site: https://glowhalal.com/auth/google/callback'),
                ]),
        ]);
    }
}
