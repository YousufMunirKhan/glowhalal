<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Per-platform publishing state. "posted_manually" is the only "done" state —
 * Phase 0 never auto-posts, the owner flips this by hand after pasting.
 */
enum SocialTargetStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Scheduled = 'scheduled';
    case PostedManually = 'posted_manually';
    case Skipped = 'skipped';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Scheduled => 'Scheduled',
            self::PostedManually => 'Posted (manually)',
            self::Skipped => 'Skipped',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Scheduled => 'info',
            self::PostedManually => 'success',
            self::Skipped => 'warning',
        };
    }
}
