<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSeoSlug;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;

#[RouteKey('slug')]
class FreeFromAttribute extends Model
{
    use HasSeoMeta, HasSeoSlug, HasSlug;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_filterable' => 'boolean',
            'has_landing_page' => 'boolean',
            'requires_verification' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['is_verified', 'verified_by_user_id', 'verified_at', 'evidence_note'])
            ->withTimestamps();
    }

    public function verifiedProducts(): BelongsToMany
    {
        return $this->products()->wherePivot('is_verified', true);
    }

    #[Scope]
    protected function filterable(Builder $q): void
    {
        $q->where('is_filterable', true)->orderBy('position');
    }
}
