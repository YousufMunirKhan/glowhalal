<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bank_name')
                    ->required(),
                TextInput::make('account_title')
                    ->required(),
                TextInput::make('account_number')
                    ->required(),
                TextInput::make('iban'),
                TextInput::make('branch_code'),
                TextInput::make('branch_name'),
                TextInput::make('instructions'),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('position')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
