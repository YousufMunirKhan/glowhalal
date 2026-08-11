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

// Publish-time cover images: scheduled posts go live automatically the moment
// their published_at passes (the published() scope handles it — no command
// needed for the publish itself). Five minutes later this generates the cover
// for any newly-live post that has none — Gemini first (when billing enables
// its quota), free Pollinations fallback — with the brand watermark stamped on.
Schedule::command('blog:generate-images')
    ->dailyAt('06:05')
    ->timezone('Asia/Karachi');

// AEO: keep Bing (and Copilot/ChatGPT search, which ride its index) fresh on
// every public URL — daily now that a post goes live every morning.
Schedule::command('indexnow:ping')
    ->dailyAt('06:20')
    ->timezone('Asia/Karachi');
