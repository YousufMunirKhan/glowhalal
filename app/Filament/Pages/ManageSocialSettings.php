<?php

namespace App\Filament\Pages;

use App\Settings\SocialSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSocialSettings extends SettingsPage
{
    protected static string $settings = SocialSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Social';

    protected static ?int $navigationSort = 60;

    protected static ?string $title = 'Social Defaults';

    protected static ?string $navigationLabel = 'Social Settings';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Composer defaults')
                ->description('Pre-filled into every new post so you type less.')
                ->schema([
                    TagsInput::make('default_hashtags')
                        ->label('Default hashtags')
                        ->helperText('Added to the hashtag list of every new post. The # is optional.'),
                    Textarea::make('brand_signature')
                        ->label('Brand signature')
                        ->rows(2)
                        ->helperText('Appended when you copy a caption from the publish helper, e.g. "— Glow Halal 🌿".'),
                    TextInput::make('default_cta_url')
                        ->label('Default call-to-action link (wa.me)')
                        ->url()
                        ->helperText('Your WhatsApp order link, e.g. https://wa.me/9230000000.'),
                ]),

            Section::make('Timezone')
                ->schema([
                    Select::make('timezone')
                        ->options(collect(\DateTimeZone::listIdentifiers())->mapWithKeys(fn ($tz) => [$tz => $tz]))
                        ->searchable()
                        ->default('Asia/Karachi')
                        ->helperText('Used by the calendar and the daily reminder. Pakistan Standard Time is Asia/Karachi.'),
                ]),
        ]);
    }
}
