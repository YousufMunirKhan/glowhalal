<?php

namespace App\Console\Commands;

use App\Models\SocialPost;
use App\Models\User;
use App\Settings\SocialSettings;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

/**
 * Phase 0 reminder — NO external API. Finds posts due today (in the store
 * timezone) plus anything overdue and not yet posted, and drops a Filament
 * database notification into the bell menu of every admin user.
 */
class SocialDueDigest extends Command
{
    protected $signature = 'social:due-digest';

    protected $description = 'Notify admins of social posts to publish today and any overdue ones.';

    public function handle(): int
    {
        // Filament's DatabaseNotification is a queued (ShouldQueue) notification.
        // On this install QUEUE_CONNECTION=database with no worker running, so a
        // normal dispatch would sit in the `jobs` table and never reach the bell.
        // This command is a one-shot reminder that MUST deliver immediately, so we
        // pin notification dispatch to the sync connection for the duration of the
        // run. (Scoped to this command only — the global queue config is untouched.)
        config(['queue.default' => 'sync']);

        $tz = app(SocialSettings::class)->timezoneOrDefault();
        $endOfToday = Carbon::now($tz)->endOfDay()->utc();

        $dueOrOverdue = SocialPost::query()
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $endOfToday)
            ->whereNotIn('status', ['posted', 'archived'])
            ->orderBy('scheduled_at')
            ->get();

        $count = $dueOrOverdue->count();

        if ($count === 0) {
            $this->info('Nothing due — no notifications sent.');

            return self::SUCCESS;
        }

        $overdue = $dueOrOverdue
            ->filter(fn (SocialPost $p) => $p->scheduled_at->isPast() && ! $p->scheduled_at->isToday())
            ->count();

        $admins = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'staff']))
            ->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin/staff users to notify.');

            return self::SUCCESS;
        }

        $body = "You have {$count} post(s) to publish today"
            .($overdue > 0 ? " (including {$overdue} overdue)." : '.');

        foreach ($admins as $admin) {
            Notification::make()
                ->title('Social posts to publish')
                ->body($body)
                ->icon('heroicon-o-megaphone')
                ->warning()
                ->actions([
                    Action::make('open')
                        ->label('Open calendar')
                        ->url(\App\Filament\Pages\SocialCalendar::getUrl(), shouldOpenInNewTab: false),
                ])
                ->sendToDatabase($admin);
        }

        $this->info("Sent digest to {$admins->count()} admin(s): {$body}");

        return self::SUCCESS;
    }
}
