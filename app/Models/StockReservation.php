<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockReservation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'quantity' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function reservable(): MorphTo
    {
        return $this->morphTo();
    }

    #[Scope]
    protected function held(Builder $q): void
    {
        $q->where('status', ReservationStatus::Held);
    }

    #[Scope]
    protected function stale(Builder $q): void
    {
        $q->where('status', ReservationStatus::Held)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }
}
