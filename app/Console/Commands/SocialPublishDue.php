<?php

namespace App\Console\Commands;

use App\Enums\SocialPlatform;
use App\Enums\SocialPostStatus;
use App\Enums\SocialTargetStatus;
use App\Models\SocialPost;
use App\Services\XApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Auto-publishes due social posts on the platforms we have an API for.
 *
 * Today that is X only — Instagram/Facebook/TikTok stay on the Phase-0 manual
 * flow (social:due-digest reminds the owner). A post is auto-published only
 * when ALL of these hold:
 *
 *   - post status is Scheduled  → the owner explicitly approved it in the
 *     admin. Draft/NeedsReview content is NEVER auto-posted, whatever its
 *     scheduled_at says: on this brand one non-compliant sentence (a cure
 *     claim, a certification claim) can cost an ad account, so the "publish"
 *     permission stays human even though the publishing itself is automatic.
 *   - compliance_checked is true → same reason, second lock.
 *   - scheduled_at has passed.
 *   - the X target is still pending/scheduled.
 *
 * Failure handling: the error is logged and the target left untouched, so the
 * next run retries — but only after a cool-off (see $coolOff below), because
 * hammering a permanent error (e.g. X's "duplicate content" 403) every 5
 * minutes would burn the free tier's small write quota on failures.
 */
class SocialPublishDue extends Command
{
    protected $signature = 'social:publish-due';

    protected $description = 'Publish approved, due social posts via platform APIs (X only for now)';

    public function handle(): int
    {
        $x = XApiClient::fromConfig();

        if (! $x) {
            $this->info('X API not configured — nothing to do (manual flow still applies).');

            return self::SUCCESS;
        }

        $due = SocialPost::query()
            ->where('status', SocialPostStatus::Scheduled)
            ->where('compliance_checked', true)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->whereHas('targets', fn ($q) => $q
                ->where('platform', SocialPlatform::X)
                ->whereIn('status', [SocialTargetStatus::Pending, SocialTargetStatus::Scheduled]))
            ->with('targets')
            ->orderBy('scheduled_at')
            ->limit(5)   // a backlog drains over a few runs instead of bursting the quota
            ->get();

        foreach ($due as $post) {
            $target = $post->targets
                ->first(fn ($t) => $t->platform === SocialPlatform::X
                    && in_array($t->status, [SocialTargetStatus::Pending, SocialTargetStatus::Scheduled], true));

            if (! $target) {
                continue;
            }

            // Cool-off: a failed attempt touches the row; retry roughly hourly,
            // not every 5-minute run.
            $coolOff = $target->updated_at?->gt(now()->subMinutes(55))
                && $target->updated_at->gt($target->created_at);

            if ($coolOff && $target->status === SocialTargetStatus::Pending) {
                continue;
            }

            $caption = trim((string) $target->effectiveCaption());

            if ($caption === '') {
                Log::warning('social:publish-due — empty caption, skipping', ['post' => $post->id]);

                continue;
            }

            // X free accounts hard-cap at 280 chars. Truncating editorial copy
            // mid-sentence is worse than not posting — leave it for the owner,
            // who will see it overdue in the digest.
            if (mb_strlen($caption) > 280) {
                Log::warning('social:publish-due — caption over 280 chars, needs manual edit', [
                    'post' => $post->id, 'length' => mb_strlen($caption),
                ]);
                $target->touch();

                continue;
            }

            try {
                $tweetId = $x->postTweet($caption);
            } catch (\Throwable $e) {
                Log::warning('social:publish-due — X post failed, will retry after cool-off', [
                    'post' => $post->id, 'error' => $e->getMessage(),
                ]);
                $target->touch();

                continue;
            }

            $target->forceFill([
                'status' => SocialTargetStatus::PostedApi,
                'posted_at' => now(),
                'external_url' => 'https://x.com/i/web/status/'.$tweetId,
            ])->save();

            $this->info("Posted social post #{$post->id} to X: {$target->external_url}");

            // When every platform target is live or skipped, the post itself is done.
            $allDone = $post->targets()
                ->get()
                ->every(fn ($t) => $t->status === SocialTargetStatus::Skipped || $t->status->isPosted());

            if ($allDone) {
                $post->forceFill([
                    'status' => SocialPostStatus::Posted,
                    'published_at' => $post->published_at ?? now(),
                ])->save();
            }
        }

        return self::SUCCESS;
    }
}
