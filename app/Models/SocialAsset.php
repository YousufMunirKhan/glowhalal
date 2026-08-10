<?php

namespace App\Models;

use App\Enums\SocialAssetSource;
use App\Enums\SocialAssetType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialAsset extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => SocialAssetType::class,
            'source_channel' => SocialAssetSource::class,
            'received_at' => 'datetime',
            'consent' => 'boolean',
            'used' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SocialPost::class);
    }

    /**
     * The hard honesty rule in code: UGC is usable ONLY with recorded consent
     * AND the consent-proof screenshot on file. Everything downstream checks
     * this, never the raw `consent` flag alone.
     */
    protected function usable(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->consent === true && filled($this->consent_proof),
        );
    }
}
