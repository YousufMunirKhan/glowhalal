<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\CouponScope;
use App\Enums\CouponType;
use App\Filament\Forms\MoneyInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([

                Section::make()->columnSpan(['lg' => 2])->schema([
                    Grid::make(2)->schema([
                        TextInput::make('code')
                            ->required()->maxLength(40)
                            ->unique(ignoreRecord: true)
                            ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                            ->dehydrateStateUsing(fn (?string $state) => strtoupper((string) $state)),
                        TextInput::make('name')->required()->maxLength(120),
                    ]),

                    Textarea::make('description')->rows(2),

                    Select::make('type')
                        ->options(CouponType::class)
                        ->default(CouponType::Percentage)
                        ->required()->native(false)->live(),

                    // The database enforces this pairing via `coupons_value_check`;
                    // matching it here turns a SQL error into a validation message.
                    TextInput::make('percentage_value')
                        ->label('Percentage off')
                        ->suffix('basis points')
                        ->numeric()
                        ->minValue(1)->maxValue(10000)
                        ->helperText('Basis points: 1500 = 15.00%.')
                        ->required(fn (Get $get) => $get('type') === CouponType::Percentage->value || $get('type') === CouponType::Percentage)
                        ->visible(fn (Get $get) => in_array($get('type'), [CouponType::Percentage, CouponType::Percentage->value], true)),

                    MoneyInput::make('fixed_amount', 'Fixed discount')
                        ->required(fn (Get $get) => in_array($get('type'), [CouponType::FixedAmount, CouponType::FixedAmount->value], true))
                        ->visible(fn (Get $get) => in_array($get('type'), [CouponType::FixedAmount, CouponType::FixedAmount->value], true)),

                    Grid::make(2)->schema([
                        MoneyInput::make('max_discount_amount', 'Maximum discount')
                            ->helperText('Caps a percentage coupon.'),
                        MoneyInput::make('min_subtotal_amount', 'Minimum spend'),
                    ]),

                    Select::make('applies_to')
                        ->label('Scope')
                        ->options(CouponScope::class)
                        ->default(CouponScope::All)
                        ->required()->native(false)->live(),

                    Select::make('products')
                        ->relationship('products', 'name')
                        ->multiple()->searchable()->preload()
                        ->visible(fn (Get $get) => in_array($get('applies_to'), [CouponScope::Products, CouponScope::Products->value], true)),

                    Select::make('categories')
                        ->relationship('categories', 'name')
                        ->multiple()->searchable()->preload()
                        ->visible(fn (Get $get) => in_array($get('applies_to'), [CouponScope::Categories, CouponScope::Categories->value], true)),
                ]),

                Section::make('Limits')->columnSpan(['lg' => 1])->schema([
                    DateTimePicker::make('starts_at')->seconds(false),
                    DateTimePicker::make('ends_at')->seconds(false),
                    TextInput::make('usage_limit')->numeric()->minValue(1)
                        ->helperText('Total redemptions across all customers. Blank = unlimited.'),
                    TextInput::make('usage_limit_per_customer')->numeric()->minValue(1),
                    Toggle::make('first_order_only'),
                    Toggle::make('exclude_discounted_items')
                        ->helperText('Skip lines already discounted by a sale price.'),
                    Toggle::make('is_active')->default(true),
                ]),
            ]),
        ]);
    }
}
