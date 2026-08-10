<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\Concerns\HandlesSimpleProductPricing;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use HandlesSimpleProductPricing;

    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->extractSimpleProductPricing($data);
    }

    protected function afterCreate(): void
    {
        $this->syncSimpleProductPricing();
    }
}
