<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSeoSlug;
use App\Models\Concerns\HasSlugHistory;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\Sluggable\HasSlug;

#[RouteKey('slug')]
class BlogPost extends Model
{
    use HasFactory, HasSeoMeta, HasSeoSlug, HasSlug, HasSlugHistory, SoftDeletes;

    // `$guarded = ['id']` already makes `locale` and `translation_group_id`
    // mass-assignable, so they need no explicit `$fillable` entry; they are cast
    // as plain strings below for clarity.
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'reading_time_minutes' => 'integer',
            'views_count' => 'integer',
            'locale' => 'string',
            'translation_group_id' => 'string',
        ];
    }

    protected function slugSourceField(): string
    {
        return 'title';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'blog_post_product');
    }

    /**
     * Published means status=published AND the publish time has actually passed.
     * Scheduled posts carry status=scheduled, but a post flipped to published
     * with a future timestamp must still stay out of public listings — otherwise
     * an embargoed post leaks into the sitemap.
     */
    #[Scope]
    protected function published(Builder $q): void
    {
        $q->where('status', PostStatus::Published)
          ->whereNotNull('published_at')
          ->where('published_at', '<=', now());
    }

    #[Scope]
    protected function featured(Builder $q): void
    {
        $q->where('is_featured', true);
    }

    /**
     * Restrict a query to one content locale — 'en' at the root, 'ur-Latn' under
     * /ur-roman. Every public blog query MUST pass through this so an English URL
     * can never surface a Roman-Urdu row (or vice versa) and duplicate itself in
     * the index.
     */
    #[Scope]
    protected function forLocale(Builder $q, string $locale): void
    {
        $q->where('locale', $locale);
    }

    /**
     * Published sibling versions of this post in OTHER languages — the members of
     * its translation_group, excluding itself. Drives the reciprocal hreflang
     * cluster and the sitemap alternates.
     *
     * Returns an empty collection when the post has no group id or no published
     * sibling, so callers can emit a self-canonical with no broken hreflang.
     *
     * @return Collection<int, static>
     */
    public function translations(): Collection
    {
        if (blank($this->translation_group_id)) {
            return collect();
        }

        return static::query()
            ->published()
            ->where('translation_group_id', $this->translation_group_id)
            ->whereKeyNot($this->getKey())
            ->orderBy('locale')
            ->get();
    }
}
