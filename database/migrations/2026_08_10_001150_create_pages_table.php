<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('slug', 220);
            $table->longText('content')->nullable();
            $table->string('template', 40)->default('default')
                  ->comment('App\Enums\PageTemplate: default|full_width|contact|faq');
            $table->string('status', 20)->default('draft')->comment('draft|published');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_system')->default(false)
                  ->comment('Privacy, terms, returns. Blocks deletion — checkout links to these.');
            $table->boolean('show_in_footer')->default(false);
            $table->boolean('show_in_header')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug', 'pages_slug_unique');
            $table->index(['status', 'show_in_footer'], 'pages_status_footer_index');
        });
    }

    public function down(): void { Schema::dropIfExists('pages'); }
};
