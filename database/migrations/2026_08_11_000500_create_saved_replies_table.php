<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reusable DM / comment reply snippets, grouped by category and language.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_replies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 20)->comment('App\Enums\SavedReplyCategory: order|delivery|price|safety|general');
            $table->string('language', 20)->default('roman_urdu')->comment('App\Enums\ContentLanguage');
            $table->text('body');
            $table->timestamps();

            $table->index(['category', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_replies');
    }
};
