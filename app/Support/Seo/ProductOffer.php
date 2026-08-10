<?php

namespace App\Support\Seo;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Money;

/**
 * Architecture §7.4 — price parity between the rendered page and
 * `Product.offers.price`.
 *
 * Both the visible price and the JSON-LD offer derive from THIS object, in the
 * same request, from the same Money instance. A price the crawler reads that
 * disagrees with the price the customer reads is a structured-data violation
 * with manual-action risk, and it is caused, every time, by formatting the
 * display price in Blade and separately querying the price for the schema.
 */
final readonly class ProductOffer
{
    public function __construct(
        public Money $price,
        public ?Money $highPrice,
        public string $sku,
        public bool $inStock,
        public ?ProductVariant $variant = null,
    ) {}

    /**
     * The offer for the variant the page renders selected — which is the
     * default variant on first paint, and must stay that way. A page that
     * opens on shade A while the schema describes shade B is a mismatch that
     * is permanent and invisible.
     */
    public static function forProduct(Product $product): self
    {
        $variants = $product->relationLoaded('variants')
            ? $product->variants->where('is_active', true)
            : $product->variants()->where('is_active', true)->get();

        $default = $product->defaultVariant
            ?? $variants->firstWhere('is_default', true)
            ?? $variants->first();

        $min = $variants->min(fn (ProductVariant $v) => $v->price_amount?->minorUnits ?? 0);
        $max = $variants->max(fn (ProductVariant $v) => $v->price_amount?->minorUnits ?? 0);

        return new self(
            price: $default?->price_amount ?? $product->price_min_amount ?? Money::zero(),
            highPrice: $min !== $max ? new Money((int) $max) : null,
            sku: $default?->sku ?? ($product->sku_prefix ?? (string) $product->id),
            inStock: (int) $product->total_stock > 0,
            variant: $default,
        );
    }

    /** True when the catalogue shows a span rather than a single price. */
    public function isRange(): bool
    {
        return $this->highPrice !== null;
    }

    /** What the customer reads: "Rs 2,450". */
    public function display(): string
    {
        return $this->price->format();
    }

    public function displayRange(): string
    {
        return $this->highPrice === null
            ? $this->price->format()
            : $this->price->format().' – '.$this->highPrice->format();
    }

    /** What schema.org reads: "2450.00" — same minor units, no second lookup. */
    public function structuredValue(): string
    {
        return number_format($this->price->minorUnits / 100, 2, '.', '');
    }

    public function structuredHighValue(): ?string
    {
        return $this->highPrice === null
            ? null
            : number_format($this->highPrice->minorUnits / 100, 2, '.', '');
    }

    public function availability(): string
    {
        return $this->inStock
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock';
    }
}
