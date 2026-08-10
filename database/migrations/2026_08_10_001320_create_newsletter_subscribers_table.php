<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Phase 6 — optional
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 180);
            $table->string('name', 120)->nullable();
            $table->string('status', 20)->default('pending')->comment('pending|subscribed|unsubscribed|bounced');
            $table->string('confirmation_token', 64)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('source', 40)->nullable();
            $table->timestamps();

            $table->unique('email', 'newsletter_subscribers_email_unique');
            $table->index('status', 'newsletter_subscribers_status_index');
        });
    }

    public function down(): void { Schema::dropIfExists('newsletter_subscribers'); }
};
