<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * UGC / testimonial intake. A piece of customer content is only ever "usable"
 * when consent = true AND a consent_proof screenshot is on file (enforced in the
 * model's `usable` accessor and in the Filament resource). No consent, no use.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_assets', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->comment('App\Enums\SocialAssetType: video|photo|text');
            $table->string('source_channel', 20)->comment('App\Enums\SocialAssetSource: whatsapp|instagram|other');
            $table->string('source_contact')->nullable()->comment('Who sent it (handle / number / name).');
            $table->timestamp('received_at')->nullable();

            // Consent is the gate. consent_proof is the screenshot of the customer
            // saying yes; without it the asset can never be marked usable.
            $table->boolean('consent')->default(false);
            $table->string('consent_proof')->nullable()->comment('Path to consent screenshot on the public disk.');
            $table->text('consent_note')->nullable();

            $table->string('customer_display_name')->nullable();
            $table->string('file')->nullable()->comment('Path to the media (photo/video) on the public disk.');
            $table->text('quote')->nullable()->comment('For text testimonials, the exact words as sent.');

            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->boolean('used')->default(false);

            $table->timestamps();

            $table->index(['consent', 'used']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_assets');
    }
};
