<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_from_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 48)->comment('alcohol_free, carmine_free, gelatin_free, ...');
            $table->string('name', 100)->comment('"Alcohol-Free"');
            $table->string('slug', 120)->comment('Drives /collections/{slug} landing pages.');
            $table->string('short_description', 255)->nullable();
            $table->text('description')->nullable()->comment('Landing-page body copy. Substantial text ranks.');
            $table->string('icon_path')->nullable();
            $table->string('badge_color', 7)->nullable();
            $table->boolean('is_filterable')->default(true)->comment('Appears in the catalogue facet sidebar.');
            $table->boolean('has_landing_page')->default(false)->comment('Included in the sitemap when true.');
            $table->boolean('requires_verification')->default(true)
                  ->comment('True = the claim cannot be published until a staff member signs off.');
            $table->unsignedInteger('products_count')->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique('code', 'free_from_attributes_code_unique');
            $table->unique('slug', 'free_from_attributes_slug_unique');
            $table->index(['is_filterable', 'position'], 'free_from_attributes_filterable_index');
        });
    }

    public function down(): void { Schema::dropIfExists('free_from_attributes'); }
};
