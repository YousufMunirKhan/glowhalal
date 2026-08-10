<?php

namespace App\Filament\Resources\ShippingZones\Schemas;

use App\Enums\PakistanProvince;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShippingZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')->required()->maxLength(120),

                Select::make('provinces')
                    ->multiple()
                    ->options(PakistanProvince::options())
                    ->native(false)
                    ->helperText('Leave empty to match on city alone.'),

                TagsInput::make('cities')
                    ->helperText('Optional city overrides, e.g. Karachi, Lahore. More specific than province.'),

                Grid::make(3)->schema([
                    TextInput::make('position')->numeric()->minValue(0)->default(0),
                    Toggle::make('is_active')->default(true),
                    Toggle::make('is_fallback')
                        ->helperText('The zone used when nothing else matches. Keep exactly one.'),
                ]),
            ]),
        ]);
    }
}
