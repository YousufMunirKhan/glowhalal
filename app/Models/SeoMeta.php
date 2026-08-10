<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $table = 'seo_metas';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_indexable' => 'boolean',
            'is_followable' => 'boolean',
            'structured_data_overrides' => 'array',
        ];
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function robotsDirective(): string
    {
        return ($this->is_indexable ? 'index' : 'noindex').','.($this->is_followable ? 'follow' : 'nofollow');
    }
}
