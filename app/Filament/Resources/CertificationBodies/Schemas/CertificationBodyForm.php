<?php

namespace App\Filament\Resources\CertificationBodies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CertificationBodyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('short_name'),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('country_code'),
                TextInput::make('website')
                    ->url(),
                TextInput::make('verification_url_template'),
                TextInput::make('logo_path'),
                TextInput::make('logo_alt'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('accreditation'),
                Toggle::make('is_recognised')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('position')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
