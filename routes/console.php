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

// Phase 2: approved + compliance-checked posts publish themselves on X the
// moment their scheduled_at passes (other platforms stay manual — the digest
// above covers them). Cheap no-op when no X keys are configured or nothing is
// due; failures retry with an hourly cool-off inside the command.
Schedule::command('social:publish-due')
    ->everyFiveMinutes();

// Inventory hygiene: hand back stock held by placed-but-never-confirmed orders
// once their 7-day reservation window lapses, so quantity_available stops
// drifting down on abandoned COD orders. Idempotent — safe to run daily.
Schedule::command('inventory:release-expired')
    ->dailyAt('03:00')
    ->timezone('Asia/Karachi');

// Queue worker, cron-driven: shared hosting cannot keep a daemon alive, so the
// scheduler drains the database queue every minute and exits. This means ONE
// hPanel cron entry (schedule:run) powers everything — queue, reminders,
// covers, IndexNow.
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()
    ->withoutOverlapping();

// Publish-time cover images: scheduled posts go live automatically the moment
// their published_at passes (the published() scope handles it — no command
// needed for the publish itself). Five minutes later this generates the cover
// for any newly-live post that has none — Gemini first (when billing enables
// its quota), free Pollinations fallback — with the brand watermark stamped on.
// 01:05 pairs with the drip's 01:00 publish moment (app timezone is
// Asia/Karachi, so published_at means Pakistan time).
Schedule::command('blog:generate-images')
    ->dailyAt('01:05')
    ->timezone('Asia/Karachi');

// Legacy-equity handover: the three old WordPress blog URLs 301 to the generic
// /blog until their recreated posts exist. This repoints each redirect at its
// recreated post THE NIGHT THAT POST GOES LIVE — never earlier (a 301 into a
// scheduled-but-unpublished slug would be a redirect to a 404, which Google
// treats as a dead end and drops the old URL's equity). Idempotent: once all
// three point at their posts, every later run is a no-op.
Artisan::command('blog:repoint-legacy-redirects', function () {
    $map = [
        '/embrace-natural-care-the-benefits-of-neem-soap' => 'neem-soap-benefits-skin',
        '/the-hidden-dangers-of-market-soaps-understanding-the-causes-of-pimples-in-pakistan' => 'pimples-in-pakistan-heat-humidity',
        '/the-hidden-dangers-of-store-bought-soaps-for-your-skin' => 'whats-really-in-your-bar-soap',
    ];

    foreach ($map as $from => $slug) {
        $live = \App\Models\BlogPost::query()->published()->where('slug', $slug)->exists();

        if (! $live) {
            continue;
        }

        $updated = \Illuminate\Support\Facades\DB::table('redirects')
            ->where('from_path', $from)
            ->where('to_path', '!=', '/blog/'.$slug)
            ->update(['to_path' => '/blog/'.$slug, 'updated_at' => now()]);

        if ($updated) {
            $this->info("Repointed {$from} -> /blog/{$slug}");
        }
    }
})->purpose('Point legacy WordPress 301s at their recreated posts once live');

Schedule::command('blog:repoint-legacy-redirects')
    ->dailyAt('01:15')
    ->timezone('Asia/Karachi');

// AEO: keep Bing (and Copilot/ChatGPT search, which ride its index) fresh on
// every public URL — daily now that a post goes live every night at 01:00.
Schedule::command('indexnow:ping')
    ->dailyAt('01:20')
    ->timezone('Asia/Karachi');
