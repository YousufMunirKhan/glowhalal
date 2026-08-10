<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum HalalStatus: string implements HasColor, HasIcon, HasLabel
{
    case Halal = 'halal';
    case Haram = 'haram';
    case Mashbooh = 'mashbooh';
    case DependsOnSource = 'depends_on_source';
    case NotApplicable = 'not_applicable';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return match ($this) {
            self::Halal => 'Halal',
            self::Haram => 'Haram',
            self::Mashbooh => 'Mashbooh (doubtful)',
            self::DependsOnSource => 'Depends on source',
            self::NotApplicable => 'Not applicable',
            self::Unknown => 'Unknown',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Halal => 'success',
            self::Haram => 'danger',
            self::Mashbooh => 'warning',
            self::DependsOnSource => 'info',
            self::NotApplicable, self::Unknown => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Halal => 'heroicon-o-check-badge',
            self::Haram => 'heroicon-o-no-symbol',
            self::Mashbooh => 'heroicon-o-question-mark-circle',
            self::DependsOnSource => 'heroicon-o-arrows-right-left',
            self::NotApplicable, self::Unknown => 'heroicon-o-minus-circle',
        };
    }

    /** Statuses that must not be presented to a customer as a settled ruling. */
    public function needsReview(): bool
    {
        return in_array($this, [self::Mashbooh, self::DependsOnSource, self::Unknown], strict: true);
    }
}
