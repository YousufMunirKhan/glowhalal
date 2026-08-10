<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\CouponScope;
use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'applies_to' => CouponScope::class,
            'fixed_amount' => MoneyCast::class,
            'max_discount_amount' => MoneyCast::class,
            'min_subtotal_amount' => MoneyCast::class,
            'percentage_value' => 'integer',
            'exclude_discounted_items' => 'boolean',
            'first_order_only' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('is_excluded');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot(['is_excluded', 'include_descendants']);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    #[Scope]
    protected function redeemable(Builder $q): void
    {
        $q->where('is_active', true)
            ->where(fn (Builder $sub) => $sub->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $sub) => $sub->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->where(fn (Builder $sub) => $sub->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'));
    }

    /** `percentage_value` is basis points: 1500 = 15.00%. */
    protected function percentageLabel(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->percentage_value === null
            ? null
            : rtrim(rtrim(number_format($this->percentage_value / 100, 2), '0'), '.').'%');
    }

    public function isExhausted(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }
}
