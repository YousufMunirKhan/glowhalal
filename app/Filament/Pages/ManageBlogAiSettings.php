<?php

namespace App\Filament\Pages;

use App\Settings\BlogSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageBlogAiSettings extends SettingsPage
{
    protected static string $settings = BlogSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Blog & AI Images';

    protected static ?string $navigationLabel = 'Blog & AI Images';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('How auto-publish works')
                ->description('No switch needed: give a blog post status "Published" with a FUTURE '
                    .'"Published at" date & time (PKT) and it goes live automatically at that exact '
                    .'moment. The daily 6:05 AM job then generates its cover image right away, and '
                    .'6:20 AM pings Bing (IndexNow) so search engines hear about it the same morning.')
                ->schema([]),

            Section::make('AI cover images')
                ->description('Covers are generated automatically at publish time and always carry the '
                    .'Glow Halal logo watermark.')
                ->schema([
                    Toggle::make('ai_images_enabled')
                        ->label('Generate AI cover images automatically')
                        ->helperText('Off = new posts publish without a cover until you upload one manually.'),

                    Select::make('image_provider')
                        ->label('Image provider priority')
                        ->options([
                            'gemini_first' => 'Gemini first, then Pollinations fallback (recommended)',
                            'pollinations_only' => 'Pollinations only (always free)',
                        ])
                        ->required()
                        ->helperText('Gemini gives the best quality but its API needs billing enabled on your '
                            .'Google project — until then every attempt falls back to the free Pollinations '
                            .'provider automatically, so images always get made either way.'),

                    TextInput::make('gemini_daily_limit')
                        ->label('Gemini images per day (max)')
                        ->numeric()->minValue(0)->maxValue(50)
                        ->helperText('Daily cap on Gemini generations — beyond it the free fallback is used. '
                            .'Keep at 1–2 for one post per day; it also caps your spend once Gemini billing is on.'),
                ]),
        ]);
    }
}
