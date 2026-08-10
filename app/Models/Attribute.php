<?php

namespace App\Models;

use App\Enums\AttributeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => AttributeType::class,
            'is_variant_defining' => 'boolean',
            'is_filterable' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('position');
    }
}
