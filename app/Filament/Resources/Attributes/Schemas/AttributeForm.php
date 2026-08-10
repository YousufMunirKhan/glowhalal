<?php

namespace App\Filament\Resources\Attributes\Schemas;

use App\Enums\AttributeType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options(AttributeType::class)
                    ->default('select')
                    ->required(),
                Toggle::make('is_variant_defining')
                    ->required(),
                Toggle::make('is_filterable')
                    ->required(),
                TextInput::make('position')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
