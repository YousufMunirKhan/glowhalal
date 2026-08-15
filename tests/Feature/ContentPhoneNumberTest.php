<?php

namespace Tests\Feature;

use App\Settings\StoreSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Guards against the bug that shipped on 13 Aug 2026 and stayed live for two
 * days: a blog post told readers to "WhatsApp at 0341-7164556" while every
 * template on the same page rendered the real 0301 2973886 from StoreSettings.
 *
 * The original fix migration only replaced the wa.me LINK digits
 * ('923417164556'), so the human-readable form in the body copy — a different
 * string entirely — survived it. Templates read the number from settings and
 * are safe; admin-authored CONTENT is where a number can be typed by hand, so
 * that is what this checks.
 *
 * Failure here means a customer reading the page would message the wrong
 * number, and that Google is seeing an inconsistent NAP for the business.
 */
class ContentPhoneNumberTest extends TestCase
{
    /**
     * Any PK mobile number written into page or post content must be the
     * store's own number.
     */
    public function test_content_never_contains_a_foreign_pakistani_mobile_number(): void
    {
        // This is a lint over REAL content, meant for a database that has some
        // (local dev, a production snapshot). The default test connection is an
        // empty in-memory sqlite with no settings table at all — resolving
        // StoreSettings there throws before content could be scanned, so skip.
        if (! Schema::hasTable('settings')) {
            $this->markTestSkipped('No settings table on this connection — content lint needs a real database.');
        }

        $store = $this->storeDigits();

        if ($store === null) {
            $this->markTestSkipped('No store WhatsApp/contact number configured.');
        }

        $offenders = [];

        foreach (['pages', 'blog_posts'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'content')) {
                continue;
            }

            $columns = ['id', 'content'];

            if (Schema::hasColumn($table, 'slug')) {
                $columns[] = 'slug';
            }

            foreach (DB::table($table)->select($columns)->get() as $row) {
                foreach ($this->mobileNumbersIn((string) $row->content) as $written) {
                    if ($this->normalise($written) !== $store) {
                        $offenders[] = sprintf(
                            '%s #%s (%s): "%s"',
                            $table,
                            $row->id,
                            $row->slug ?? 'no slug',
                            $written,
                        );
                    }
                }
            }
        }

        $this->assertSame([], $offenders, sprintf(
            "Content contains %d phone number(s) that are not the store number (%s):\n  - %s\n\n".
            'Fix the body copy in the admin, then re-run. Templates are not affected — '.
            'they read the number from StoreSettings.',
            count($offenders),
            $store,
            implode("\n  - ", $offenders),
        ));
    }

    /**
     * Every written form of a PK mobile found in $content.
     *
     * @return list<string>
     */
    private function mobileNumbersIn(string $content): array
    {
        // `(?<!\d)` rather than `(?<![\d\/])` so that a wrong number inside a
        // wa.me/<digits> link is caught too — that form is how the number is
        // most often written, and excluding it is what let the 13 Aug fix pass
        // while the page was still wrong.
        preg_match_all(
            '/(?<!\d)(?:\+?92|0092|0)[\s.\-]?3\d{2}[\s.\-]?\d{7}(?![\d])/',
            $content,
            $matches,
        );

        return array_values(array_unique($matches[0]));
    }

    /** Bare international digits, e.g. "0341-7164556" → "923417164556". */
    private function normalise(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number) ?? '';

        if (str_starts_with($digits, '0092')) {
            return substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            return '92'.substr($digits, 1);
        }

        return $digits;
    }

    private function storeDigits(): ?string
    {
        $settings = app(StoreSettings::class);
        $raw = $settings->whatsapp_number ?: $settings->contact_phone;

        if (blank($raw)) {
            return null;
        }

        $digits = $this->normalise($raw);

        return strlen($digits) === 12 ? $digits : null;
    }
}
