<?php

namespace App\Filament\Resources\SocialAssets\Pages;

use App\Filament\Resources\SocialAssets\SocialAssetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSocialAssets extends ManageRecords
{
    protected static string $resource = SocialAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
