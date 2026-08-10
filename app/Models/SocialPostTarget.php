<?php

namespace App\Models;

use App\Enums\SocialPlatform;
use App\Enums\SocialTargetStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPostTarget extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'platform' => SocialPlatform::class,
            'status' => SocialTargetStatus::class,
            'posted_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class, 'social_post_id');
    }

    /** The caption actually used on this platform: override if set, else base. */
    public function effectiveCaption(): ?string
    {
        return filled($this->caption_override)
            ? $this->caption_override
            : $this->post?->caption_base;
    }
}
