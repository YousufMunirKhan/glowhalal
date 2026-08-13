<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Abandoned-cart RECOVERY over WhatsApp. The carts table already keeps `email`
 * (for the email path); this adds the phone + name captured progressively at
 * checkout so the owner can WhatsApp a buyer who entered their details but did
 * not place the order — the highest-intent, most recoverable segment. Captured
 * on field blur, so it adds ZERO friction to checkout.
 *
 * `recovery_contacted_at` records that the owner already reached out, so the
 * admin list can hide it and no one gets messaged twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email')
                ->comment('Captured at checkout — enables abandoned-cart WhatsApp recovery.');
            $table->string('customer_name', 200)->nullable()->after('phone');
            $table->timestamp('recovery_contacted_at')->nullable()->after('abandoned_email_sent_at')
                ->comment('Owner has already followed up on this cart — do not contact again.');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['phone', 'customer_name', 'recovery_contacted_at']);
        });
    }
};
