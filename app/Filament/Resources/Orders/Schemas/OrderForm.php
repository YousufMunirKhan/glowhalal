<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\PakistanProvince;
use App\Enums\PaymentStatus;
use App\Filament\Forms\MoneyInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

/**
 * Deliberately narrow. §4.7: orders are immutable commercial records and the
 * admin performs *transitions*, not edits — a free-form page over `orders`
 * invites someone to change a total after the fact.
 *
 * What is editable here is only what is genuinely operational: who to contact,
 * where it is going, and the courier reference. Status, totals and payment
 * state are changed exclusively through the actions on the View page.
 */
class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Text::make('Status, totals and payment state are changed through the actions on the order view page, never here.')
                    ->color('warning'),
            ])->columnSpanFull(),

            Grid::make(['default' => 1, 'lg' => 3])->schema([

                Section::make('Customer')->columnSpan(['lg' => 2])->schema([
                    Grid::make(2)->schema([
                        TextInput::make('customer_name')->required()->maxLength(150),
                        TextInput::make('email')->email()->required()->maxLength(255),
                        TextInput::make('phone')->tel()->required()->maxLength(30),
                        Select::make('user_id')
                            ->label('Registered account')
                            ->relationship('user', 'name')
                            ->searchable()->preload()->native(false)
                            ->placeholder('Guest order'),
                    ]),

                    Textarea::make('customer_note')->rows(2)->columnSpanFull(),
                    Textarea::make('admin_note')->label('Internal note')->rows(2)->columnSpanFull(),
                ]),

                Section::make('Fulfilment')->columnSpan(['lg' => 1])->schema([
                    Select::make('payment_method')
                        ->options([
                            'cod' => 'Cash on delivery',
                            'bank_transfer' => 'Bank transfer',
                            'jazzcash' => 'JazzCash',
                            'easypaisa' => 'Easypaisa',
                        ])
                        ->required()->native(false),

                    TextInput::make('shipping_method_name')->label('Shipping method')->maxLength(120),
                    TextInput::make('tracking_number')->maxLength(80),
                    Select::make('courier')
                        ->options([
                            'tcs' => 'TCS',
                            'leopards' => 'Leopards',
                            'mp' => 'M&P',
                            'postex' => 'PostEx',
                            'other' => 'Other',
                        ])
                        ->native(false),
                    TextInput::make('tracking_url')->url()->maxLength(255),
                ]),
            ]),

            Section::make('Totals')
                ->description('Entered in rupees. Only editable while the order is still pending.')
                ->schema([
                    Grid::make(3)->schema([
                        self::money('subtotal_amount', 'Subtotal')->required(),
                        self::money('discount_amount', 'Discount'),
                        self::money('shipping_amount', 'Shipping'),
                        self::money('cod_fee_amount', 'COD fee'),
                        self::money('tax_amount', 'Tax'),
                        self::money('grand_total_amount', 'Grand total')->required(),
                    ]),
                ])
                ->disabled(fn (?\App\Models\Order $record) => $record !== null && $record->status !== OrderStatus::Pending),

            Section::make('Shipping address')
                ->relationship('shippingAddress')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('first_name')->required()->maxLength(80),
                        TextInput::make('last_name')->maxLength(80),
                        TextInput::make('phone')->tel()->required()->maxLength(30),
                        TextInput::make('alternate_phone')->tel()->maxLength(30),
                    ]),
                    TextInput::make('line_1')->required()->maxLength(255)->columnSpanFull(),
                    TextInput::make('line_2')->maxLength(255)->columnSpanFull(),
                    Grid::make(3)->schema([
                        TextInput::make('area')->maxLength(120),
                        TextInput::make('city')->required()->maxLength(120),
                        Select::make('province')
                            ->options(PakistanProvince::class)
                            ->required()->native(false),
                    ]),
                    Textarea::make('delivery_instructions')->rows(2)->columnSpanFull(),
                ])
                ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                    $data['type'] = 'shipping';
                    $data['country_code'] ??= 'PK';

                    return $data;
                }),

            // Set on create only; the observer generates order_number and public_token.
            Select::make('status')
                ->options(OrderStatus::class)
                ->default(OrderStatus::Pending)
                ->required()->native(false)
                ->visibleOn('create')
                ->helperText('After creation, status changes only through the transition actions.'),

            Select::make('payment_status')
                ->options(PaymentStatus::class)
                ->default(PaymentStatus::Pending)
                ->required()->native(false)
                ->visibleOn('create'),
        ]);
    }

    /** Money columns are integer paisa; the admin types rupees. */
    private static function money(string $name, string $label): TextInput
    {
        return MoneyInput::make($name, $label)->default(0);
    }
}
