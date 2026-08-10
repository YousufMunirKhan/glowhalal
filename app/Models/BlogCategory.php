<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSeoSlug;
use App\Models\Concerns\HasSlugHistory;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;

#[RouteKey('slug')]
class BlogCategory extends Model
{
    use HasFactory, HasSeoMeta, HasSeoSlug, HasSlug, HasSlugHistory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'position' => 'integer',
            'posts_count' => 'integer',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    #[Scope]
    protected function active(Builder $q): void
    {
        $q->where('is_active', true);
    }

    #[Scope]
    protected function ordered(Builder $q): void
    {
        $q->orderBy('position')->orderBy('name');
    }
}
