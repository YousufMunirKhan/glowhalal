<?php

namespace App\Models;

use App\Models\Concerns\HasSeoSlug;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Sluggable\HasSlug;

#[RouteKey('slug')]
class Tag extends Model
{
    use HasFactory, HasSeoSlug, HasSlug;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'usage_count' => 'integer',
        ];
    }

    public function posts(): MorphToMany
    {
        return $this->morphedByMany(BlogPost::class, 'taggable');
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'taggable');
    }

    /**
     * `type` namespaces the tag vocabulary — a "Cleansers" blog tag and a
     * "Cleansers" product tag are separate rows. The unique index is on
     * (slug, type), so scoping by type is required before any slug lookup.
     */
    #[Scope]
    protected function ofType(Builder $q, string $type): void
    {
        $q->where('type', $type);
    }
}
