<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certification_bodies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);                 // "SANHA Halal Associates Pakistan"
            $table->string('short_name', 40)->nullable();// "SANHA"
            $table->string('slug', 200);
            $table->char('country_code', 2)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('verification_url_template', 512)->nullable()
                  ->comment('e.g. https://body.example/verify?cert={certificate_number} — {certificate_number} is substituted.');
            $table->string('logo_path')->nullable();
            $table->string('logo_alt', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('accreditation', 255)->nullable()
                  ->comment('Accrediting authority, if any. Verify before publishing — see note below.');
            $table->boolean('is_recognised')->default(true)
                  ->comment('False = listed for transparency but not presented as an authority.');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique('slug', 'certification_bodies_slug_unique');
            $table->index(['is_active', 'position'], 'certification_bodies_active_position_index');
        });
    }

    public function down(): void { Schema::dropIfExists('certification_bodies'); }
};
