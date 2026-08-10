<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Named, reusable bundles of hashtags the composer can drop into a caption.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hashtag_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('tags')->nullable();
            $table->string('language', 20)->default('mixed')->comment('App\Enums\ContentLanguage');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hashtag_sets');
    }
};
