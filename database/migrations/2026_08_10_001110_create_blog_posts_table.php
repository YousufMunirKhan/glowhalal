<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 220);
            $table->string('slug', 240);
            $table->string('excerpt', 500)->nullable()
                  ->comment('Plain text. Feeds listing cards and the meta description fallback.');
            $table->longText('content')->nullable()->comment('Filament RichEditor output. See §4.6.');
            $table->string('cover_image_path')->nullable();
            $table->string('cover_image_alt', 255)->nullable();
            $table->string('status', 20)->default('draft')
                  ->comment('App\Enums\PostStatus: draft|scheduled|published|archived');
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('reading_time_minutes')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_comments')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug', 'blog_posts_slug_unique');
            $table->index(['status', 'published_at'], 'blog_posts_status_published_index');
            $table->index(['blog_category_id', 'status', 'published_at'], 'blog_posts_category_status_index');
            $table->index(['status', 'is_featured'], 'blog_posts_featured_index');
            $table->fullText(['title', 'excerpt'], 'blog_posts_fulltext_index');
        });
    }

    public function down(): void { Schema::dropIfExists('blog_posts'); }
};
