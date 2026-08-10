<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Phase 6 — optional
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete()
                  ->comment('Present = verified purchase, which is what earns the rich-snippet badge.');
            $table->string('author_name', 120);
            $table->unsignedTinyInteger('rating')->comment('1–5');
            $table->string('title', 200)->nullable();
            $table->text('body')->nullable();
            $table->json('image_paths')->nullable();
            $table->string('status', 20)->default('pending')->comment('pending|approved|rejected|spam');
            $table->unsignedInteger('helpful_count')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'status', 'created_at'], 'product_reviews_product_status_index');
            $table->unique(['product_id', 'order_id', 'user_id'], 'product_reviews_one_per_purchase_unique');
        });

        DB::statement("ALTER TABLE product_reviews ADD CONSTRAINT product_reviews_rating_check CHECK (rating BETWEEN 1 AND 5)");
    }

    public function down(): void { Schema::dropIfExists('product_reviews'); }
};
