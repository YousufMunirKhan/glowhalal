<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Social planner (Phase 0): every morning, remind admins of posts due today
// and anything overdue. No external API — writes Filament database notifications.
Schedule::command('social:due-digest')
    ->dailyAt('08:00')
    ->timezone('Asia/Karachi');

// Inventory hygiene: hand back stock held by placed-but-never-confirmed orders
// once their 7-day reservation window lapses, so quantity_available stops
// drifting down on abandoned COD orders. Idempotent — safe to run daily.
Schedule::command('inventory:release-expired')
    ->dailyAt('03:00')
    ->timezone('Asia/Karachi');
