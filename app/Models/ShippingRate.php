<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\ShippingRateType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => ShippingRateType::class,
            'amount' => MoneyCast::class,
            'free_over_subtotal_amount' => MoneyCast::class,
            'cod_surcharge_amount' => MoneyCast::class,
            'is_active' => 'boolean',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function deliveryEstimate(): ?string
    {
        return match (true) {
            $this->min_delivery_days === null && $this->max_delivery_days === null => null,
            $this->min_delivery_days === $this->max_delivery_days => "{$this->min_delivery_days} days",
            default => "{$this->min_delivery_days}–{$this->max_delivery_days} days",
        };
    }
}
