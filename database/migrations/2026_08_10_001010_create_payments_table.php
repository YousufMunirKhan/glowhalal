<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('driver', 40)->comment('Payment driver key — matches config/payments.php');
            $table->string('status', 24)->default('pending')
                  ->comment('App\Enums\PaymentAttemptStatus: pending|awaiting_verification|authorized|paid|failed|cancelled|refunded');
            $table->char('currency', 3)->default('PKR');
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('refunded_amount')->default(0);

            $table->string('reference', 120)->nullable()
                  ->comment('Customer-supplied: bank transaction id, JazzCash TxnRefNo.');
            $table->string('gateway_transaction_id', 120)->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('idempotency_key', 64)->nullable()
                  ->comment('Guards against duplicate charges on webhook replay or double-submit.');

            $table->json('gateway_request')->nullable();
            $table->json('gateway_response')->nullable()->comment('Redact secrets before writing. See §6.6.');
            $table->string('failure_code', 60)->nullable();
            $table->string('failure_message', 500)->nullable();

            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('Manual transfers get a payment window.');
            $table->timestamps();

            $table->unique('idempotency_key', 'payments_idempotency_unique');
            $table->index(['order_id', 'status'], 'payments_order_status_index');
            $table->index(['driver', 'status'], 'payments_driver_status_index');
            $table->index('gateway_transaction_id', 'payments_gateway_txn_index');
        });
    }

    public function down(): void { Schema::dropIfExists('payments'); }
};
