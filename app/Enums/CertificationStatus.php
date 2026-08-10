<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CertificationStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Expiring = 'expiring';
    case Expired = 'expired';
    case Pending = 'pending';
    case Revoked = 'revoked';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Expiring => 'Expiring soon',
            self::Expired => 'Expired',
            self::Pending => 'Pending',
            self::Revoked => 'Revoked',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Expiring => 'warning',
            self::Expired, self::Revoked => 'danger',
            self::Pending => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Active => 'heroicon-o-shield-check',
            self::Expiring => 'heroicon-o-clock',
            self::Expired => 'heroicon-o-shield-exclamation',
            self::Pending => 'heroicon-o-ellipsis-horizontal-circle',
            self::Revoked => 'heroicon-o-x-circle',
        };
    }

    /** Only an active certificate may be rendered as a live claim on the storefront. */
    public function isRenderable(): bool
    {
        return $this === self::Active;
    }
}
