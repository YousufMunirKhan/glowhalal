<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_body_id')->constrained()->restrictOnDelete();
            $table->string('certificate_number', 120);
            $table->string('scope', 255)->nullable()
                  ->comment('What the certificate actually covers — the product, the facility, or the manufacturer.');
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('status', 20)->default('active')
                  ->comment('App\Enums\CertificationStatus: active|expiring|expired|pending|revoked');

            $table->string('document_disk', 32)->default('public');
            $table->string('document_path', 255)->nullable();
            $table->string('document_mime', 64)->nullable();
            $table->unsignedInteger('document_size_bytes')->nullable();
            $table->string('document_alt', 255)->nullable();
            $table->boolean('is_publicly_visible')->default(true)
                  ->comment('Some certificates carry supplier commercial terms — allow uploading without publishing.');

            $table->string('verification_url', 512)->nullable()->comment('Resolved from the body template, or overridden.');
            $table->text('notes')->nullable()->comment('Internal. Never rendered to the storefront.');
            $table->foreignId('added_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'certification_body_id', 'certificate_number'], 'product_certifications_unique');
            $table->index(['product_id', 'status'], 'product_certifications_product_status_index');
            $table->index(['status', 'expires_at'], 'product_certifications_status_expires_index');
            $table->index('expires_at', 'product_certifications_expires_index');
        });
    }

    public function down(): void { Schema::dropIfExists('product_certifications'); }
};
