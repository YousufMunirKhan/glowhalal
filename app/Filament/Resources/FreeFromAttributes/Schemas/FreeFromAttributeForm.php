<?php

namespace App\Filament\Resources\FreeFromAttributes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FreeFromAttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('short_description'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('icon_path'),
                TextInput::make('badge_color'),
                Toggle::make('is_filterable')
                    ->required(),
                Toggle::make('has_landing_page')
                    ->required(),
                Toggle::make('requires_verification')
                    ->required(),
                TextInput::make('products_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('position')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
