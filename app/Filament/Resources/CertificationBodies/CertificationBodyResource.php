<?php

namespace App\Filament\Resources\CertificationBodies;

use App\Filament\Resources\CertificationBodies\Pages\CreateCertificationBody;
use App\Filament\Resources\CertificationBodies\Pages\EditCertificationBody;
use App\Filament\Resources\CertificationBodies\Pages\ListCertificationBodies;
use App\Filament\Resources\CertificationBodies\Schemas\CertificationBodyForm;
use App\Filament\Resources\CertificationBodies\Tables\CertificationBodiesTable;
use App\Models\CertificationBody;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CertificationBodyResource extends Resource
{
    protected static ?string $model = CertificationBody::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = 'Halal';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CertificationBodyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CertificationBodiesTable::configure($table);
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
            'index' => ListCertificationBodies::route('/'),
            'create' => CreateCertificationBody::route('/create'),
            'edit' => EditCertificationBody::route('/{record}/edit'),
        ];
    }
}
