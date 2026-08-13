<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Privacy policy catch-up for Google Consent Mode v2. The Google tag now loads
 * for every visitor and, WITHOUT consent, sends anonymous cookieless signals
 * (no cookies, no identifiers) that Google uses for aggregated modelling — the
 * old policy text implied nothing at all reached Google before consent. It also
 * now names Google Ads alongside Analytics. Same honesty rule as ever: the
 * policy must describe what the site actually does.
 *
 * Rewrites the three affected sentences in the live `pages` row (the seeder
 * carries the same text for fresh installs). Idempotent: each str_replace
 * no-ops once the text is fixed, and a page whose owner has since rewritten
 * these passages is left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pages', 'content')) {
            return;
        }

        $replacements = [
            '<strong>Usage &amp; device data</strong> collected, with your consent, through cookies and Google Analytics — such as pages viewed and general location — to understand and improve the store.'
                => '<strong>Usage &amp; device data</strong> — such as pages viewed and general location — to understand and improve the store. With your consent this is collected through cookies (Google Analytics); if you decline, no cookies are set and Google receives only anonymous, cookieless signals with no personal identifiers, used for aggregated statistics (Google Consent Mode).',

            'With your consent — given through our cookie banner — we also use <strong>analytics cookies</strong> (Google Analytics) to measure and improve the site. If you decline, analytics cookies are not set.'
                => 'With your consent — given through our cookie banner — we also use <strong>analytics and advertising cookies</strong> (Google Analytics, Google Ads) to measure and improve the site and our advertising. If you decline, no analytics or advertising cookies are set and no personal identifiers are collected — Google receives only anonymous, cookieless signals used for aggregated statistics (Google Consent Mode). Any Meta (Facebook) advertising tools run only with your consent.',

            '<strong>Google</strong> — for optional sign-in and, with your consent, analytics, under Google\'s own privacy terms.'
                => '<strong>Google</strong> — for optional sign-in and, with your consent, analytics and advertising measurement, under Google\'s own privacy terms. Without consent, Google receives only anonymous, cookieless signals.',
        ];

        foreach (DB::table('pages')->select('id', 'content')->get() as $row) {
            $fixed = str_replace(array_keys($replacements), array_values($replacements), (string) $row->content);

            if ($fixed !== $row->content) {
                DB::table('pages')->where('id', $row->id)->update(['content' => $fixed]);
            }
        }
    }

    public function down(): void
    {
        // One-way content fix; nothing to roll back.
    }
};
