<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SlugHistory extends Model
{
    /** Append-only — a superseded slug is never edited. */
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    public function sluggable(): MorphTo
    {
        return $this->morphTo();
    }
}
