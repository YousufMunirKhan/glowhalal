<?php

namespace App\Models;

use App\Enums\HalalStatus;
use App\Enums\IngredientOrigin;
use App\Enums\PostStatus;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSeoSlug;
use App\Models\Concerns\HasSlugHistory;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;

#[RouteKey('slug')]
class Ingredient extends Model
{
    use HasFactory, HasSeoMeta, HasSeoSlug, HasSlug, HasSlugHistory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'aliases' => 'array',
            'origin' => IngredientOrigin::class,
            'halal_status' => HalalStatus::class,
            'status' => PostStatus::class,
            'published_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'is_animal_derived' => 'boolean',
            'is_alcohol' => 'boolean',
            'is_common_allergen' => 'boolean',
            'is_key_ingredient_candidate' => 'boolean',
            'has_glossary_page' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['position', 'concentration_percent', 'is_key_ingredient', 'source_note', 'resolved_halal_status']);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /** "See also" cluster links for /halal-ingredients/{slug} — §2.7. */
    public function related(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'ingredient_related', 'ingredient_id', 'related_ingredient_id')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    /** Only rows that are a real, published page at /halal-ingredients/{slug}. */
    #[Scope]
    protected function published(Builder $q): void
    {
        $q->where('has_glossary_page', true)
            ->where('status', PostStatus::Published)
            ->where(fn (Builder $sub) => $sub->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    #[Scope]
    protected function questionable(Builder $q): void
    {
        $q->whereIn('halal_status', [HalalStatus::Mashbooh, HalalStatus::DependsOnSource, HalalStatus::Unknown]);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => \Illuminate\Support\Facades\Route::has('ingredients.show')
            ? route('ingredients.show', $this->slug)
            : url("/halal-ingredients/{$this->slug}")
        )->shouldCache();
    }
}
