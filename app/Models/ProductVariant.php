<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Observers\ProductVariantObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(ProductVariantObserver::class)]
class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price_amount' => MoneyCast::class,
            'compare_at_amount' => MoneyCast::class,
            'cost_amount' => MoneyCast::class,
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'track_inventory' => 'boolean',
            'allow_backorder' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class)->with('attribute');
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(InventoryItem::class)
            ->whereHas('location', fn (Builder $q) => $q->where('is_default', true));
    }

    #[Scope]
    protected function purchasable(Builder $query): void
    {
        $query->where('is_active', true)
            ->whereHas('product', fn (Builder $q) => $q->published());
    }

    protected function availableQuantity(): Attribute
    {
        return Attribute::get(fn (): int => $this->track_inventory
            ? (int) ($this->inventory?->quantity_available ?? 0)
            : PHP_INT_MAX
        )->shouldCache();
    }

    protected function isInStock(): Attribute
    {
        return Attribute::get(fn (): bool => $this->allow_backorder || $this->available_quantity > 0)->shouldCache();
    }

    /** Snapshot payload for cart_items.options_snapshot and order_items.options_snapshot. */
    public function optionsSnapshot(): array
    {
        return $this->attributeValues->map(fn (AttributeValue $v) => [
            'attribute' => $v->attribute->name,
            'value' => $v->value,
            'hex' => $v->hex_color,
        ])->all();
    }

    public function displayName(): string
    {
        return trim(($this->product?->name ?? '').' — '.($this->name ?? $this->sku), ' —');
    }
}
