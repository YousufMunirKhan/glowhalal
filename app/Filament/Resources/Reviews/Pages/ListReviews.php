<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Reviews\ReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    // No CreateAction: reviews come from customers, not staff. Moderate here.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
