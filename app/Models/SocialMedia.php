<?php

namespace App\Models;

use App\Enums\SocialMediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialMedia extends Model
{
    protected $table = 'social_media';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => SocialMediaType::class,
            'position' => 'integer',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class, 'social_post_id');
    }
}
