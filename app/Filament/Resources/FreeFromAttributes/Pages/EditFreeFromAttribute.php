<?php

namespace App\Filament\Resources\FreeFromAttributes\Pages;

use App\Filament\Resources\FreeFromAttributes\FreeFromAttributeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFreeFromAttribute extends EditRecord
{
    protected static string $resource = FreeFromAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
