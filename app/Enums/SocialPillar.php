<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Content pillars — the recurring "kinds" of post the brand plans around.
 */
enum SocialPillar: string implements HasLabel
{
    case HowTo = 'how_to';
    case ComfortMassage = 'comfort_massage';
    case SeasonalSkincare = 'seasonal_skincare';
    case CustomerQa = 'customer_qa';
    case BehindTheScenes = 'behind_the_scenes';

    public function getLabel(): string
    {
        return match ($this) {
            self::HowTo => 'How-to / usage tip',
            self::ComfortMassage => 'Comfort & massage',
            self::SeasonalSkincare => 'Seasonal skincare',
            self::CustomerQa => 'Customer Q&A',
            self::BehindTheScenes => 'Behind the scenes',
        };
    }
}
