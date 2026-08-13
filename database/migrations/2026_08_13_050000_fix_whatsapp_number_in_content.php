<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One WhatsApp number, everywhere. The store's real number lives in
 * StoreSettings (+92 301 2973886 → wa.me/923012973886) and every template reads
 * it from there — but two admin-authored pages (FAQ, Reviews) and some older
 * seeded copy hard-coded stale numbers (923417164556 and the 923001234567
 * placeholder). This rewrites any such number inside page/blog CONTENT to the
 * real one. Idempotent (str_replace no-ops once fixed).
 */
return new class extends Migration
{
    public function up(): void
    {
        $wrong = ['923417164556', '923001234567'];
        $right = '923012973886';

        foreach (['pages', 'blog_posts'] as $table) {
            if (! \Illuminate\Support\Facades\Schema::hasColumn($table, 'content')) {
                continue;
            }

            foreach (DB::table($table)->select('id', 'content')->get() as $row) {
                $fixed = str_replace($wrong, $right, (string) $row->content);

                if ($fixed !== $row->content) {
                    DB::table($table)->where('id', $row->id)->update(['content' => $fixed]);
                }
            }
        }
    }

    public function down(): void
    {
        // One-way content fix; nothing to roll back.
    }
};
