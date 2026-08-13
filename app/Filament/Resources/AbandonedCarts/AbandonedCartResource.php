<?php

namespace App\Filament\Resources\AbandonedCarts;

use App\Filament\Resources\AbandonedCarts\Pages\ListAbandonedCarts;
use App\Filament\Resources\AbandonedCarts\Tables\AbandonedCartsTable;
use App\Models\Cart;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Abandoned-cart recovery over WhatsApp. Lists carts where a shopper entered
 * their phone at checkout but never placed the order — the highest-intent
 * segment to win back. Each row has a one-tap "Message on WhatsApp" button with
 * a pre-filled, honest recovery message; the owner sends it from their own
 * WhatsApp (Web or phone). "Mark contacted" removes it from the queue so no one
 * is messaged twice. Read-only otherwise — no editing a live shopper's cart.
 */
class AbandonedCartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 42;

    protected static ?string $modelLabel = 'abandoned cart';

    protected static ?string $slug = 'abandoned-carts';

    /** Scope the whole resource to recoverable carts, items eager-loaded. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->recoverable()
            ->with(['items.variant.product'])
            ->latest('last_activity_at');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Cart::query()->recoverable()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return AbandonedCartsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAbandonedCarts::route('/'),
        ];
    }
}
