<?php

namespace App\Models;

use App\Models\Concerns\HasSeoSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;

class AttributeValue extends Model
{
    use HasSeoSlug, HasSlug;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['position' => 'integer'];
    }

    /** Slug is generated from `value`, not `name`. */
    protected function slugSourceField(): string
    {
        return 'value';
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class);
    }
}
