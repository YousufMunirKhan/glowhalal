<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()
                  ->constrained('categories')->restrictOnDelete();
            $table->string('name', 160);
            $table->string('slug', 180);
            $table->string('path', 512)->nullable()
                  ->comment('Materialised ancestor ids, e.g. "1/7/23". Maintained by CategoryObserver.');
            $table->unsignedTinyInteger('depth')->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_alt', 255)->nullable();
            $table->string('icon_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_in_menu')->default(true);
            $table->unsignedInteger('products_count')->default(0)
                  ->comment('Denormalised active-product count incl. descendants. Rebuilt nightly.');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug', 'categories_slug_unique');
            $table->index(['parent_id', 'position'], 'categories_parent_position_index');
            $table->index('path', 'categories_path_index');
            $table->index(['is_active', 'show_in_menu'], 'categories_active_menu_index');
        });
    }

    public function down(): void { Schema::dropIfExists('categories'); }
};
