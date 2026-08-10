<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SocialPostStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case NeedsReview = 'needs_review';
    case Scheduled = 'scheduled';
    case Posted = 'posted';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::NeedsReview => 'Needs review',
            self::Scheduled => 'Scheduled',
            self::Posted => 'Posted',
            self::Archived => 'Archived',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::NeedsReview => 'warning',
            self::Scheduled => 'info',
            self::Posted => 'success',
            self::Archived => 'danger',
        };
    }

    /**
     * Statuses that must not be reachable until the compliance checklist has
     * been completed. Enforced in the form (see SocialPostForm).
     */
    public function requiresCompliance(): bool
    {
        return in_array($this, [self::NeedsReview, self::Scheduled, self::Posted], true);
    }
}
