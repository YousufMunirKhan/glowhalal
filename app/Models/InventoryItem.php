<?php

namespace App\Models;

use App\Observers\InventoryItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

#[ObservedBy(InventoryItemObserver::class)]
class InventoryItem extends Model
{
    protected $guarded = ['id'];

    /** Stored generated columns — MySQL rejects any INSERT/UPDATE that names them. */
    public const GENERATED_COLUMNS = ['quantity_available'];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'integer',
            'quantity_reserved' => 'integer',
            'quantity_available' => 'integer',
            'reorder_level' => 'integer',
            'reorder_quantity' => 'integer',
            'last_counted_at' => 'datetime',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'inventory_location_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class)->latest('created_at');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    #[Scope]
    protected function lowStock(Builder $q): void
    {
        $q->whereColumn('quantity_available', '<=', 'reorder_level');
    }

    protected function getAttributesForInsert(): array
    {
        return Arr::except(parent::getAttributesForInsert(), self::GENERATED_COLUMNS);
    }

    public function getDirty(): array
    {
        return Arr::except(parent::getDirty(), self::GENERATED_COLUMNS);
    }
}
