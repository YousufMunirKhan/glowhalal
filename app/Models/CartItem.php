<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Observers\CartItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(CartItemObserver::class)]
class CartItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'unit_price_amount' => MoneyCast::class,
            'line_total_amount' => MoneyCast::class,
            'options_snapshot' => 'array',
            'quantity' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /** True when the live price has moved since this line was added. Surfaced at checkout. */
    protected function hasPriceChanged(): Attribute
    {
        return Attribute::get(fn (): bool => $this->variant !== null
            && $this->variant->price_amount?->minorUnits !== $this->unit_price_amount?->minorUnits
        );
    }
}
