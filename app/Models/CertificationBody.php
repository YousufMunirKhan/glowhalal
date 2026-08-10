<?php

namespace App\Models;

use App\Models\Concerns\HasSeoSlug;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;

#[RouteKey('slug')]
class CertificationBody extends Model
{
    use HasSeoSlug, HasSlug;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_recognised' => 'boolean',
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(ProductCertification::class);
    }

    #[Scope]
    protected function active(Builder $q): void
    {
        $q->where('is_active', true);
    }

    public function displayName(): string
    {
        return $this->short_name ? "{$this->name} ({$this->short_name})" : $this->name;
    }
}
