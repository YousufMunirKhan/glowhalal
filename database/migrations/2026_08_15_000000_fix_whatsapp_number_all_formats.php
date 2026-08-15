<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Second pass at the stale WhatsApp number — the 13 Aug migration only caught
 * the wa.me LINK form.
 *
 * `2026_08_13_050000_fix_whatsapp_number_in_content` did a str_replace on the
 * literal strings '923417164556' and '923001234567'. That fixed every
 * wa.me/<digits> href, but body copy writes the number the way a human types
 * it — "0341-7164556" — which shares no substring with '923417164556'. So the
 * clickable links were corrected while the READABLE number stayed wrong, and
 * /blog/lookman-e-hayat-oil-price-in-pakistan has been telling visitors
 * "Order online at glowhalal.com or on WhatsApp at 0341-7164556" ever since.
 *
 * This pass matches any Pakistani mobile number in ANY common written form
 * (+92 341 7164556, 0341-7164556, 92-341-7164556, 03417164556 …) and rewrites
 * it to the store's real number, preserving the separator style the author
 * used so the surrounding prose still reads naturally.
 *
 * The store number is read from StoreSettings at runtime rather than hardcoded,
 * so this cannot itself go stale if the owner changes it later. Idempotent:
 * numbers that already match the store number are left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        $store = $this->storeDigits();

        if ($store === null) {
            // No number configured — rewriting to an empty string would be
            // worse than leaving the stale one visible for a human to catch.
            return;
        }

        foreach (['pages', 'blog_posts'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'content')) {
                continue;
            }

            foreach (DB::table($table)->select('id', 'content')->get() as $row) {
                $content = (string) $row->content;
                $fixed = $this->rewrite($content, $store);

                if ($fixed !== $content) {
                    DB::table($table)->where('id', $row->id)->update(['content' => $fixed]);
                }
            }
        }
    }

    public function down(): void
    {
        // One-way content fix; the previous value was wrong by definition.
    }

    /**
     * The store's number as bare digits in international form (923012973886),
     * or null when nothing usable is configured.
     */
    private function storeDigits(): ?string
    {
        $raw = null;

        try {
            $settings = app(\App\Settings\StoreSettings::class);
            $raw = $settings->whatsapp_number ?: $settings->contact_phone;
        } catch (\Throwable) {
            // Settings table may not exist yet on a fresh install; fall through
            // to the DB read below rather than failing the whole migration.
        }

        if (blank($raw) && Schema::hasTable('settings')) {
            $row = DB::table('settings')
                ->where('group', 'store')
                ->whereIn('name', ['whatsapp_number', 'contact_phone'])
                ->orderByRaw("name = 'whatsapp_number' desc")
                ->value('payload');

            $raw = is_string($row) ? trim(json_decode($row) ?? '', '"') : null;
        }

        if (blank($raw)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $raw);

        // Normalise 03012973886 → 923012973886 so comparisons are like-for-like.
        if (str_starts_with($digits, '0')) {
            $digits = '92'.substr($digits, 1);
        }

        return strlen($digits) === 12 ? $digits : null;
    }

    /**
     * Rewrite every PK mobile number in $content that is not already the store
     * number, keeping the author's separator style.
     */
    private function rewrite(string $content, string $store): string
    {
        // +92 341 7164556 | 0092-341-7164556 | 92 341 7164556 | 0341-7164556 |
        // 03417164556 | wa.me/923417164556. A bare "341 7164556" with no
        // country/trunk prefix is deliberately NOT matched (too loose).
        //
        // The lookbehind is `(?<!\d)` and NOT `(?<![\d\/])`: excluding a
        // preceding slash would skip `wa.me/923417164556`, which is the single
        // most important thing to rewrite. The digit lookaround alone is what
        // stops a match inside a longer number such as an order id.
        $pattern = '/(?<!\d)(\+?92|0092|0)([\s.\-]?)(3\d{2})([\s.\-]?)(\d{7})(?![\d])/';

        return preg_replace_callback($pattern, function (array $m) use ($store) {
            $digits = '92'.$m[3].$m[5];

            if ($digits === $store) {
                return $m[0];   // already correct — leave the author's formatting alone
            }

            $local = substr($store, 2);          // 3012973886
            $prefix = $m[1];                     // +92 | 92 | 0092 | 0
            $sepA = $m[2];
            $sepB = $m[4];

            // Rebuild in the same shape the author used.
            return $prefix === '0'
                ? '0'.$sepA.substr($local, 0, 3).$sepB.substr($local, 3)
                : $prefix.$sepA.substr($local, 0, 3).$sepB.substr($local, 3);
        }, $content) ?? $content;
    }
};
