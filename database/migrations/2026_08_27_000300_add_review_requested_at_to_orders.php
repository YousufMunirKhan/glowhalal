<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks that the owner has already WhatsApp'd this customer for a review, so
 * the Admin → Orders "Request review" action can hide once used and no one is
 * asked twice. Part of the claim-free review funnel that rebuilds the ⭐ rating
 * snippet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('review_requested_at')->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('review_requested_at');
        });
    }
};
