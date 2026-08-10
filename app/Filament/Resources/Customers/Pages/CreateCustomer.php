<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    /** A record created here must carry the role the resource filters on. */
    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->getRecord();
        $user->assignRole('customer');
    }
}
