<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Per-platform publishing state. Two "done" states: "posted_manually" (the
 * owner pasted it into the app themselves — the Phase-0 flow, still how
 * Instagram/Facebook/TikTok work) and "posted_api" (social:publish-due pushed
 * it through the platform's API — X only, as of Aug 2026).
 */
enum SocialTargetStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Scheduled = 'scheduled';
    case PostedManually = 'posted_manually';
    case PostedApi = 'posted_api';
    case Skipped = 'skipped';

    /** Either way, it is live — the digest and "all done?" checks want this. */
    public function isPosted(): bool
    {
        return $this === self::PostedManually || $this === self::PostedApi;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Scheduled => 'Scheduled',
            self::PostedManually => 'Posted (manually)',
            self::PostedApi => 'Posted (auto)',
            self::Skipped => 'Skipped',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Scheduled => 'info',
            self::PostedManually => 'success',
            self::PostedApi => 'success',
            self::Skipped => 'warning',
        };
    }
}
