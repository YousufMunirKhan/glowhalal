<?php

namespace App\Filament\Resources\HashtagSets\Pages;

use App\Filament\Resources\HashtagSets\HashtagSetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHashtagSets extends ManageRecords
{
    protected static string $resource = HashtagSetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
