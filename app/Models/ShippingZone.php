<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'provinces' => 'array',
            'cities' => 'array',
            'is_fallback' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class)->orderBy('position');
    }

    #[Scope]
    protected function active(Builder $q): void
    {
        $q->where('is_active', true)->orderBy('position');
    }
}
