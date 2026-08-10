<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Filament\Support\Identifier;
use App\Models\Order;
use App\Support\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            // The infolist root defaults to TWO columns on a ViewRecord page.
            // Left at the default, every root-level section is laid into half
            // the page — which is what squeezed this page to ~280px columns at
            // a 1280px viewport. Force one column and let each block below
            // decide its own split.
            ->columns(1)
            ->components([
                Grid::make(['default' => 1, 'xl' => 3])->schema([

                    Section::make('Order')->columnSpan(['xl' => 2])->schema([
                        // Two columns, never three. At 1280px the Order card is
                        // ~570px wide; halves are ~280px, which comfortably fits
                        // an email address. Thirds are ~190px, which does not.
                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            Identifier::entry('order_number', 'Order number')->weight(FontWeight::Bold),
                            TextEntry::make('status')->badge(),
                            TextEntry::make('payment_status')->label('Payment')->badge(),
                            TextEntry::make('payment_method')->label('Payment method')->badge(),
                        ]),

                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            TextEntry::make('customer_name')->label('Customer'),
                            Identifier::entry('email', 'Email'),
                            Identifier::entry('phone', 'Phone'),
                            TextEntry::make('courier')->placeholder('—'),
                        ]),

                        Identifier::entry('tracking_number', 'Tracking number')
                            ->placeholder('—')
                            ->columnSpanFull(),

                        TextEntry::make('customer_note')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('admin_note')->label('Internal note')->placeholder('—')->columnSpanFull(),
                    ]),

                    Section::make('Totals')->columnSpan(['xl' => 1])->schema([
                        // Two columns at every size. Nine single-column rows made
                        // this card far taller than the Order card beside it,
                        // leaving a large dead gap; paired rows bring the two
                        // cards to roughly the same height.
                        Grid::make(['default' => 2, 'md' => 3, 'xl' => 2])->schema([
                            self::money('subtotal_amount', 'Subtotal'),
                            self::money('discount_amount', 'Discount'),
                            self::money('shipping_amount', 'Shipping'),
                            self::money('cod_fee_amount', 'COD fee'),
                            self::money('tax_amount', 'Tax'),
                            self::money('grand_total_amount', 'Grand total')->weight(FontWeight::Bold),
                            self::money('paid_amount', 'Paid'),
                            self::money('refunded_amount', 'Refunded'),
                            TextEntry::make('balance_due')
                                ->label('Balance due')
                                ->state(fn (Order $record) => $record->balance_due->format())
                                ->weight(FontWeight::Bold)
                                ->color(fn (Order $record) => $record->balance_due->isZero() ? 'success' : 'warning'),
                        ]),
                    ]),
                ]),

                Section::make('Shipping address')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'md' => 2, 'xl' => 4])
                    ->schema([
                        TextEntry::make('shippingAddress.line_1')->label('Address')->placeholder('—'),
                        TextEntry::make('shippingAddress.city')->label('City')->placeholder('—'),
                        TextEntry::make('shippingAddress.province')->label('Province')->placeholder('—'),
                        TextEntry::make('shippingAddress.delivery_instructions')->label('Instructions')->placeholder('—'),
                    ]),

                Section::make('Status history')->collapsible()->columnSpanFull()->schema([
                    RepeatableEntry::make('statusHistories')
                        ->hiddenLabel()
                        ->schema([
                            Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])->schema([
                                TextEntry::make('created_at')->label('When')->dateTime('d M Y H:i'),
                                TextEntry::make('from_status')->label('From')->badge()->placeholder('—'),
                                TextEntry::make('to_status')->label('To')->badge(),
                                TextEntry::make('note')->label('Note')->placeholder('—'),
                            ]),
                        ]),
                ]),
            ]);
    }

    private static function money(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->state(fn (Order $record) => $record->{$name} instanceof Money
                ? $record->{$name}->format()
                : '—');
    }
}
