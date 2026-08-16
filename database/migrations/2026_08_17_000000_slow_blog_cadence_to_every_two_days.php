<?php

use App\Models\BlogPost;
use Illuminate\Database\Migrations\Migration;

/**
 * Slows the editorial drip from one post per night to one every two days,
 * at the owner's instruction, starting 19 Aug 2026.
 *
 * Why this is the right call rather than a loss of momentum: Search Console
 * currently reports 23 pages "Crawled — currently not indexed" and zero new
 * pages indexed. The bottleneck is not content volume — it is that a domain
 * with no backlinks, no Business Profile and no brand searches has given
 * Google no reason to index it yet. Adding pages faster into an unindexed pile
 * does not fix that, and a zero-authority site publishing nineteen posts in
 * six days is the shape Google's scaled-content guidance is written about.
 * Halving the rate keeps the same content, on the same alternating EN /
 * Roman-Urdu rhythm, at a pace a real small brand plausibly sustains.
 *
 * Nothing is deleted and no post is dropped: the twelve pending posts keep
 * their order and their language alternation, spread across 19 Aug - 10 Sep.
 *
 * Explicit slug => date mapping (not an offset) so the migration is idempotent
 * and produces the same calendar however many times it runs.
 */
return new class extends Migration
{
    private const SCHEDULE = [
        'best-herbal-face-cream-pakistan' => '2026-08-19',
        'chehray-ke-liye-behtareen-cream' => '2026-08-21',
        'how-to-identify-original-lookman-e-hayat-oil' => '2026-08-23',
        'neem-sabun-ke-fayde' => '2026-08-25',
        'pimples-in-pakistan-heat-humidity' => '2026-08-27',
        'salajeet-ke-fayde-aur-istemal' => '2026-08-29',
        'lookman-e-hayat-oil-for-face-honest-answer' => '2026-08-31',
        'kalonji-ke-fayde-balon-ke-liye' => '2026-09-02',
        'neem-soap-benefits-skin' => '2026-09-04',
        'balon-ka-tel-banane-ka-tarika' => '2026-09-06',
        'whats-really-in-your-bar-soap' => '2026-09-08',
        'dano-wali-jild-ki-hifazat' => '2026-09-10',
    ];

    public function up(): void
    {
        foreach (self::SCHEDULE as $slug => $date) {
            $post = BlogPost::withoutGlobalScopes()->where('slug', $slug)->first();

            // Never reschedule a post that is already live — moving a published
            // URL's date would be a lie to readers and to dateModified.
            if (! $post || $post->published_at?->isPast()) {
                continue;
            }

            BlogPost::withoutEvents(fn () => $post->forceFill([
                'published_at' => $date.' 01:00:00',
            ])->save());
        }
    }

    public function down(): void
    {
        // One-way: the previous nightly calendar is not worth restoring, and
        // re-compressing the schedule is the opposite of this change's intent.
    }
};
