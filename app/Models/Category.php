<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSeoSlug;
use App\Models\Concerns\HasSlugHistory;
use App\Observers\CategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

#[ObservedBy(CategoryObserver::class)]
#[RouteKey('slug')]
class Category extends Model
{
    use HasFactory, HasRecursiveRelationships, HasSeoMeta, HasSeoSlug, HasSlug, HasSlugHistory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'show_in_menu' => 'boolean',
            'depth' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('position');
    }

    #[Scope]
    protected function active(Builder $q): void
    {
        $q->where('is_active', true);
    }

    #[Scope]
    protected function roots(Builder $q): void
    {
        $q->whereNull('parent_id');
    }

    #[Scope]
    protected function inMenu(Builder $q): void
    {
        $q->where('is_active', true)->where('show_in_menu', true);
    }

    /** `'1/7/23/%'` — feeds the indexed descendant lookup used by Product::inCategory(). */
    public function descendantPathPrefix(): string
    {
        return trim(($this->path ? $this->path.'/' : '').$this->id, '/').'/%';
    }

    /** Breadcrumbs without recursion: one query, ordered by the materialised path. */
    public function ancestorTrail(): \Illuminate\Support\Collection
    {
        $ids = array_filter(explode('/', (string) $this->path));

        return $ids === []
            ? collect()
            : static::query()->whereIn('id', $ids)->orderByRaw('FIELD(id, '.implode(',', $ids).')')->get();
    }

    /** Indented label for hierarchical selects in the admin. */
    public function indentedName(): string
    {
        return str_repeat('— ', (int) $this->depth).$this->name;
    }
}
