<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->comment('Stored uppercase. Normalised by a mutator.');
            $table->string('name', 120)->nullable()->comment('Internal label for the admin list.');
            $table->string('description', 255)->nullable()->comment('Shown to the customer on apply.');
            $table->string('type', 20)
                  ->comment('App\Enums\CouponType: percentage|fixed_amount|free_shipping');
            $table->unsignedInteger('percentage_value')->nullable()->comment('Basis points: 1500 = 15.00%');
            $table->unsignedBigInteger('fixed_amount')->nullable()->comment('paisa');
            $table->unsignedBigInteger('max_discount_amount')->nullable()
                  ->comment('paisa. Caps a percentage coupon. Without this, 50% off a bulk order is unbounded.');
            $table->unsignedBigInteger('min_subtotal_amount')->default(0);

            $table->string('applies_to', 20)->default('all')
                  ->comment('App\Enums\CouponScope: all|products|categories');
            $table->boolean('exclude_discounted_items')->default(false)
                  ->comment('Prevents stacking a coupon on top of a compare_at markdown.');

            $table->unsignedInteger('usage_limit')->nullable()->comment('NULL = unlimited');
            $table->unsignedInteger('usage_limit_per_customer')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('first_order_only')->default(false);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('code', 'coupons_code_unique');
            $table->index(['is_active', 'starts_at', 'ends_at'], 'coupons_active_window_index');
        });

        DB::statement("
            ALTER TABLE coupons ADD CONSTRAINT coupons_value_check CHECK (
                (type = 'percentage'   AND percentage_value IS NOT NULL AND percentage_value BETWEEN 1 AND 10000) OR
                (type = 'fixed_amount' AND fixed_amount IS NOT NULL AND fixed_amount > 0) OR
                (type = 'free_shipping')
            )
        ");
    }

    public function down(): void { Schema::dropIfExists('coupons'); }
};
