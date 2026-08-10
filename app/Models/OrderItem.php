<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'options_snapshot' => 'array',
            'halal_snapshot' => 'array',
            'quantity' => 'integer',
            'quantity_refunded' => 'integer',
            'unit_price_amount' => MoneyCast::class,
            'line_subtotal_amount' => MoneyCast::class,
            'line_discount_amount' => MoneyCast::class,
            'line_tax_amount' => MoneyCast::class,
            'line_total_amount' => MoneyCast::class,
            'unit_cost_amount' => MoneyCast::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
