<?php

namespace App\Filament\Resources\FreeFromAttributes;

use App\Filament\Resources\FreeFromAttributes\Pages\CreateFreeFromAttribute;
use App\Filament\Resources\FreeFromAttributes\Pages\EditFreeFromAttribute;
use App\Filament\Resources\FreeFromAttributes\Pages\ListFreeFromAttributes;
use App\Filament\Resources\FreeFromAttributes\Schemas\FreeFromAttributeForm;
use App\Filament\Resources\FreeFromAttributes\Tables\FreeFromAttributesTable;
use App\Models\FreeFromAttribute;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FreeFromAttributeResource extends Resource
{
    protected static ?string $model = FreeFromAttribute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static string|UnitEnum|null $navigationGroup = 'Halal';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FreeFromAttributeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FreeFromAttributesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFreeFromAttributes::route('/'),
            'create' => CreateFreeFromAttribute::route('/create'),
            'edit' => EditFreeFromAttribute::route('/{record}/edit'),
        ];
    }
}
