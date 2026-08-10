<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->ulid('token')->comment('Opaque public identifier stored in the cart cookie.');
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('active')
                  ->comment('App\Enums\CartStatus: active|converted|abandoned|merged');
            $table->char('currency', 3)->default('PKR');

            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code', 40)->nullable()->comment('Snapshot — survives coupon deletion.');

            $table->unsignedBigInteger('subtotal_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('shipping_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('grand_total_amount')->default(0);
            $table->unsignedInteger('items_count')->default(0);

            $table->string('email', 180)->nullable()->comment('Captured at checkout step 1 — enables abandoned-cart email.');
            $table->foreignId('merged_into_cart_id')->nullable()
                  ->constrained('carts')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('abandoned_email_sent_at')->nullable();
            $table->timestamps();

            $table->unique('token', 'carts_token_unique');
            $table->index(['user_id', 'status'], 'carts_user_status_index');
            $table->index(['status', 'last_activity_at'], 'carts_status_activity_index');
            $table->index(['status', 'expires_at'], 'carts_status_expires_index');
        });
    }

    public function down(): void { Schema::dropIfExists('carts'); }
};
