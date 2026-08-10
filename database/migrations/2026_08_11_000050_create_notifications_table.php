<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laravel's standard notifications table. It was not present in the base schema,
 * and the Social planner's `social:due-digest` command writes Filament database
 * notifications here so admins see "N posts to publish today" in the bell menu.
 *
 * Guarded with hasTable so it is a no-op if another migration/agent adds it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notifications')) {
            return;
        }

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Intentionally NOT dropped: other features may rely on this core table.
    }
};
