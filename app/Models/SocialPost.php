<?php

namespace App\Models;

use App\Enums\ContentLanguage;
use App\Enums\SocialCtaType;
use App\Enums\SocialPillar;
use App\Enums\SocialPostStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialPost extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => SocialPostStatus::class,
            'pillar' => SocialPillar::class,
            'language' => ContentLanguage::class,
            'cta_type' => SocialCtaType::class,
            'hashtags' => 'array',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
            'contains_sensitive_content' => 'boolean',
            'compliance_checked' => 'boolean',
            'uses_ugc' => 'boolean',
        ];
    }

    public function targets(): HasMany
    {
        return $this->hasMany(SocialPostTarget::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(SocialMedia::class)->orderBy('position');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(SocialAsset::class, 'social_asset_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
