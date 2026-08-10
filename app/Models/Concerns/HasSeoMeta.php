<?php

namespace App\Models\Concerns;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeoMeta
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    /** Meta title with a sane fallback to the model's own display field. */
    public function resolvedMetaTitle(): ?string
    {
        return $this->seoMeta?->meta_title
            ?? $this->getAttribute('name')
            ?? $this->getAttribute('title');
    }

    public function resolvedMetaDescription(): ?string
    {
        return $this->seoMeta?->meta_description
            ?? $this->getAttribute('short_description')
            ?? $this->getAttribute('excerpt')
            ?? $this->getAttribute('description');
    }

    public function isIndexable(): bool
    {
        return (bool) ($this->seoMeta?->is_indexable ?? true);
    }
}
