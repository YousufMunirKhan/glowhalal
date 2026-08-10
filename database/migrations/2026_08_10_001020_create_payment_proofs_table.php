<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('disk', 32)->default('private');
            $table->string('path', 255);
            $table->string('original_filename', 255)->nullable();
            $table->string('mime_type', 64)->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->unsignedBigInteger('declared_amount')->nullable();
            $table->date('declared_paid_on')->nullable();
            $table->string('declared_reference', 120)->nullable();
            $table->string('status', 20)->default('pending')->comment('pending|approved|rejected');
            $table->string('rejection_reason', 500)->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['payment_id', 'status'], 'payment_proofs_payment_status_index');
            $table->index('status', 'payment_proofs_status_index');
        });
    }

    public function down(): void { Schema::dropIfExists('payment_proofs'); }
};
