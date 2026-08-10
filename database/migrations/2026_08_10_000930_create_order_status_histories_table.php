<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 24)->nullable();
            $table->string('to_status', 24);
            $table->string('from_payment_status', 24)->nullable();
            $table->string('to_payment_status', 24)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()
                  ->comment('NULL = system transition (webhook, scheduled job).');
            $table->string('note', 500)->nullable();
            $table->boolean('customer_notified')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['order_id', 'created_at'], 'order_status_histories_order_created_index');
        });
    }

    public function down(): void { Schema::dropIfExists('order_status_histories'); }
};
