<?php

namespace App\Filament\Resources\Redirects\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('from_path')
                    ->required(),
                TextInput::make('to_path')
                    ->required(),
                TextInput::make('status_code')
                    ->required()
                    ->numeric()
                    ->default(301),
                TextInput::make('source')
                    ->required()
                    ->default('manual'),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('hits')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('last_hit_at'),
            ]);
    }
}
