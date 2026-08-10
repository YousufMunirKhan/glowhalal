<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path', 512)->comment('Normalised: leading slash, no query string, no trailing slash.');
            $table->string('to_path', 512);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->string('source', 20)->default('manual')->comment('manual|slug_change|import');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();

            $table->unique('from_path', 'redirects_from_path_unique');
            $table->index(['is_active', 'from_path'], 'redirects_active_path_index');
        });
    }

    public function down(): void { Schema::dropIfExists('redirects'); }
};
