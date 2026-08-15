<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\CertificationStatus;
use App\Enums\ProductStatus;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSeoSlug;
use App\Models\Concerns\HasSlugHistory;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
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
use Spatie\Sluggable\HasSlug;

#[ObservedBy(ProductObserver::class)]
#[RouteKey('slug')]
class Product extends Model
{
    use HasFactory, HasSeoMeta, HasSeoSlug, HasSlug, HasSlugHistory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'published_at' => 'datetime',
            'price_min_amount' => MoneyCast::class,
            'price_max_amount' => MoneyCast::class,
            'compare_at_max_amount' => MoneyCast::class,
            'is_featured' => 'boolean',
            'is_new_arrival' => 'boolean',
            'reviews_average' => 'decimal:2',
            'faqs' => 'array',
            'faqs_ur' => 'array',
        ];
    }

    /**
     * Whether this product has a publishable Roman Urdu page. The UR URL only
     * exists when the owner has written real UR content — a slug alone (or a
     * name alone) must never produce a page of English text on a /ur-roman URL.
     */
    public function hasRomanUrdu(): bool
    {
        return filled($this->slug_ur) && filled($this->name_ur) && filled($this->description_ur);
    }

    /**
     * Swap the content-bearing attributes for their Roman Urdu counterparts,
     * in memory only, so the show view, WhatsApp message, analytics data-*
     * attributes and JSON-LD all render UR text without a single view change.
     * `slug` is deliberately NOT swapped — route generation must keep working —
     * and nothing on the GET path ever saves the model.
     */
    public function presentInRomanUrdu(): void
    {
        $this->setAttribute('name', $this->name_ur);
        $this->setAttribute('short_description', $this->short_description_ur ?: $this->short_description);
        $this->setAttribute('description', $this->description_ur);
        $this->setAttribute('how_to_use', $this->how_to_use_ur ?: $this->how_to_use);
        $this->setAttribute('faqs', $this->faqs_ur ?: []);
    }

    // ---- Catalogue relations -------------------------------------------------

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot('position')->orderByPivot('position');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('position');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->ofMany([
            'is_default' => 'max',
            'position' => 'min',
        ], fn (Builder $q) => $q->where('is_active', true));
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->ofMany(['is_primary' => 'max', 'position' => 'min']);
    }

    // ---- Halal relations -----------------------------------------------------

    public function halalProfile(): HasOne
    {
        return $this->hasOne(ProductHalalProfile::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(ProductCertification::class);
    }

    public function activeCertifications(): HasMany
    {
        return $this->certifications()
            ->where('status', CertificationStatus::Active)
            ->where('is_publicly_visible', true);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)
            ->withPivot(['position', 'concentration_percent', 'is_key_ingredient', 'source_note', 'resolved_halal_status'])
            ->withTimestamps()
            ->orderByPivot('position');   // INCI order — never re-sort
    }

    public function keyIngredients(): BelongsToMany
    {
        return $this->ingredients()->wherePivot('is_key_ingredient', true);
    }

    public function freeFromAttributes(): BelongsToMany
    {
        return $this->belongsToMany(FreeFromAttribute::class)
            ->withPivot(['is_verified', 'verified_at', 'evidence_note'])
            ->withTimestamps();
    }

    /** Storefront-safe: only verified claims are ever renderable. */
    public function verifiedFreeFromAttributes(): BelongsToMany
    {
        return $this->freeFromAttributes()->wherePivot('is_verified', true)->orderBy('position');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function blogPosts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class);
    }

    // ---- Scopes (Laravel 13 attribute form) ----------------------------------

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', ProductStatus::Active)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    #[Scope]
    protected function inStock(Builder $query): void
    {
        $query->where('total_stock', '>', 0);
    }

    #[Scope]
    protected function inCategory(Builder $query, Category $category, bool $includeDescendants = true): void
    {
        $query->whereHas('categories', function (Builder $q) use ($category, $includeDescendants) {
            $includeDescendants
                ? $q->where('categories.id', $category->id)
                    ->orWhere('categories.path', 'like', $category->descendantPathPrefix())
                : $q->where('categories.id', $category->id);
        });
    }

    #[Scope]
    protected function freeFrom(Builder $query, string ...$codes): void
    {
        foreach ($codes as $code) {
            $query->whereHas('freeFromAttributes', fn (Builder $q) => $q
                ->where('code', $code)
                ->where('free_from_attribute_product.is_verified', true));
        }
    }

    #[Scope]
    protected function certified(Builder $query): void
    {
        $query->whereHas('halalProfile', fn (Builder $q) => $q->where('is_certified', true));
    }

    #[Scope]
    protected function search(Builder $query, string $term): void
    {
        $query->whereFullText(['name', 'short_description'], $term);
    }

    // ---- Accessors -----------------------------------------------------------

    protected function priceRange(): Attribute
    {
        return Attribute::get(function (): string {
            $min = $this->price_min_amount;
            $max = $this->price_max_amount;

            if ($min === null && $max === null) {
                return '—';
            }

            $min ??= $max;
            $max ??= $min;

            return $min->minorUnits === $max->minorUnits
                ? $min->format()
                : "{$min->format()} – {$max->format()}";
        })->shouldCache();
    }

    protected function isOnSale(): Attribute
    {
        return Attribute::get(fn (): bool => $this->compare_at_max_amount !== null
            && $this->price_max_amount !== null
            && $this->compare_at_max_amount->minorUnits > $this->price_max_amount->minorUnits
        )->shouldCache();
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => \Illuminate\Support\Facades\Route::has('products.show')
            ? route('products.show', $this->slug)
            : url("/products/{$this->slug}")
        )->shouldCache();
    }
}
