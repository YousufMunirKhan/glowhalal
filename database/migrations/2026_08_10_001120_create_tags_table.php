<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 100);
            $table->string('type', 40)->nullable()->comment('blog|product — keeps namespaces separate.');
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();

            $table->unique(['slug', 'type'], 'tags_slug_type_unique');
        });
    }

    public function down(): void { Schema::dropIfExists('tags'); }
};
