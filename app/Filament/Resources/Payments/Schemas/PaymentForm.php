<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentAttemptStatus;
use App\Filament\Forms\MoneyInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Grid::make(2)->schema([
                    Select::make('order_id')
                        ->label('Order')
                        ->relationship('order', 'order_number')
                        ->searchable()->preload()->native(false)
                        ->required(),

                    Select::make('driver')
                        ->options([
                            'cod' => 'Cash on delivery',
                            'bank_transfer' => 'Bank transfer',
                            'jazzcash' => 'JazzCash',
                            'easypaisa' => 'Easypaisa',
                        ])
                        ->required()->native(false),
                ]),

                Grid::make(3)->schema([
                    MoneyInput::make('amount', 'Amount')->required()->default(0),
                    Select::make('status')
                        ->options(PaymentAttemptStatus::class)
                        ->default(PaymentAttemptStatus::Pending)
                        ->required()->native(false),
                    Select::make('bank_account_id')
                        ->label('Bank account')
                        ->relationship('bankAccount', 'account_title')
                        ->searchable()->preload()->native(false),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('reference')->maxLength(120)
                        ->helperText('Bank transaction reference supplied by the customer.'),
                    DateTimePicker::make('paid_at')->seconds(false),
                ]),

                Textarea::make('failure_message')->rows(2)->columnSpanFull(),
            ]),
        ]);
    }
}
