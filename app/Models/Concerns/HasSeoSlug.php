<?php

namespace App\Models\Concerns;

use Spatie\Sluggable\SlugOptions;

trait HasSeoSlug
{
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom($this->slugSourceField())
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(180)
            ->doNotGenerateSlugsOnUpdate();   // the SEO-critical line. See §7.1.
    }

    protected function slugSourceField(): string
    {
        return 'name';
    }
}
