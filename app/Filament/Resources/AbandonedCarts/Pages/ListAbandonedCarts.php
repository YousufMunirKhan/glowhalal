<?php

namespace App\Filament\Resources\AbandonedCarts\Pages;

use App\Filament\Resources\AbandonedCarts\AbandonedCartResource;
use Filament\Resources\Pages\ListRecords;

class ListAbandonedCarts extends ListRecords
{
    protected static string $resource = AbandonedCartResource::class;

    // No create action — these are real shopper carts, followed up, not authored.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
