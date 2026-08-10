<?php

namespace App\Filament\Resources\FreeFromAttributes\Pages;

use App\Filament\Resources\FreeFromAttributes\FreeFromAttributeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFreeFromAttributes extends ListRecords
{
    protected static string $resource = FreeFromAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
