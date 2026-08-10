<?php

namespace App\Filament\Resources\SavedReplies\Pages;

use App\Filament\Resources\SavedReplies\SavedReplyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSavedReplies extends ManageRecords
{
    protected static string $resource = SavedReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
