<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Static onboarding page for the Social planner. No model, no table — just a
 * plain, honest walkthrough of the Phase-0 (manual publishing) flow plus the
 * official links the owner needs to set up their accounts. Placed first in the
 * Social group (lowest navigationSort) so it's the first thing an owner sees.
 */
class SocialGettingStarted extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRocketLaunch;

    protected static string|UnitEnum|null $navigationGroup = 'Social';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Getting Started';

    protected static ?string $navigationLabel = 'Getting Started';

    protected string $view = 'filament.pages.social-getting-started';
}
