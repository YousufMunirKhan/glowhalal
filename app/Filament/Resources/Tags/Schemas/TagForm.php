<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(120),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(140)
                    ->helperText('The URL-friendly version of the name.'),
                TextInput::make('type')
                    ->maxLength(60)
                    ->helperText('Optional grouping, e.g. "blog" or "product". Leave blank if not needed.'),
                // usage_count is maintained automatically from how many records
                // use the tag — it is not edited by hand, so it is not a field here.
            ]);
    }
}
