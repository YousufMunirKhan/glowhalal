<?php

namespace App\Models;

use App\Enums\PageTemplate;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSeoSlug;
use App\Models\Concerns\HasSlugHistory;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;

#[RouteKey('slug')]
class Page extends Model
{
    use HasFactory, HasSeoMeta, HasSeoSlug, HasSlug, HasSlugHistory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'template' => PageTemplate::class,
            'published_at' => 'datetime',
            'is_system' => 'boolean',
            'show_in_footer' => 'boolean',
            'show_in_header' => 'boolean',
            'position' => 'integer',
        ];
    }

    protected function slugSourceField(): string
    {
        return 'title';
    }

    #[Scope]
    protected function published(Builder $q): void
    {
        $q->where('status', 'published')
          ->where(fn (Builder $sub) => $sub->whereNull('published_at')
              ->orWhere('published_at', '<=', now()));
    }

    #[Scope]
    protected function inFooter(Builder $q): void
    {
        $q->where('show_in_footer', true)->orderBy('position');
    }

    #[Scope]
    protected function inHeader(Builder $q): void
    {
        $q->where('show_in_header', true)->orderBy('position');
    }

    /**
     * System pages (About, Contact, Privacy) are referenced by slug in navigation
     * and templates. Deleting one would break those links, so the Filament
     * resource blocks deletion rather than relying on an admin remembering.
     */
    public function isDeletable(): bool
    {
        return ! $this->is_system;
    }
}
