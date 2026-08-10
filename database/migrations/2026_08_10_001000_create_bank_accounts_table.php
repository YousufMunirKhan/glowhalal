<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name', 120);            // "Meezan Bank"
            $table->string('account_title', 160);
            $table->string('account_number', 40);
            $table->string('iban', 34)->nullable();
            $table->string('branch_code', 20)->nullable();
            $table->string('branch_name', 160)->nullable();
            $table->string('instructions', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'position'], 'bank_accounts_active_position_index');
        });
    }

    public function down(): void { Schema::dropIfExists('bank_accounts'); }
};
