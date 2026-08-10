<?php

namespace App\Models;

use App\Enums\CertificationStatus;
use App\Observers\ProductCertificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(ProductCertificationObserver::class)]
class ProductCertification extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'status' => CertificationStatus::class,
            'is_publicly_visible' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function body(): BelongsTo
    {
        return $this->belongsTo(CertificationBody::class, 'certification_body_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    #[Scope]
    protected function active(Builder $q): void
    {
        $q->where('status', CertificationStatus::Active);
    }

    #[Scope]
    protected function expiringWithin(Builder $q, int $days): void
    {
        $q->whereNotNull('expires_at')->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    protected function daysUntilExpiry(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->expires_at === null
            ? null
            : (int) $this->expires_at->diffInDays(now(), absolute: false));
    }

    protected function resolvedVerificationUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->verification_url
            ?? ($this->body?->verification_url_template
                ? str_replace('{certificate_number}', urlencode($this->certificate_number), $this->body->verification_url_template)
                : null));
    }
}
