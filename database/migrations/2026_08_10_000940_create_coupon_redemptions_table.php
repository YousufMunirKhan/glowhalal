<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email', 180)->comment('Enforces per-customer limits for guest checkouts.');
            $table->unsignedBigInteger('discount_amount');
            $table->timestamp('redeemed_at')->useCurrent();

            $table->unique(['coupon_id', 'order_id'], 'coupon_redemptions_coupon_order_unique');
            $table->index(['coupon_id', 'user_id'], 'coupon_redemptions_coupon_user_index');
            $table->index(['coupon_id', 'email'], 'coupon_redemptions_coupon_email_index');
        });
    }

    public function down(): void { Schema::dropIfExists('coupon_redemptions'); }
};
