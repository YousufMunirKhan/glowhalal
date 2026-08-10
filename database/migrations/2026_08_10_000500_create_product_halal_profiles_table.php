<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_halal_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->string('overall_status', 24)->default('unknown')
                  ->comment('App\Enums\HalalStatus. Derived from ingredients + certifications, admin-overridable.');
            $table->boolean('is_certified')->default(false)
                  ->comment('Denormalised: has at least one active product_certification. Maintained by observer.');
            $table->boolean('is_self_declared')->default(false)
                  ->comment('True = manufacturer statement without third-party certification. Must be labelled as such.');

            $table->string('alcohol_status', 24)->default('none')
                  ->comment('App\Enums\AlcoholStatus: none|fatty_alcohol_only|denatured|ethanol|unknown');
            $table->boolean('is_wudu_friendly')->default(false)
                  ->comment('Water-permeable formulation permitting ablution. Applies to nail and long-wear products.');
            $table->boolean('is_vegan')->default(false);
            $table->boolean('is_cruelty_free')->default(false);

            $table->string('manufacturing_country', 2)->nullable();
            $table->string('manufacturer_name', 180)->nullable();
            $table->boolean('shared_facility_warning')->default(false)
                  ->comment('Produced on lines also handling non-halal inputs. Disclose it.');

            $table->text('summary')->nullable()->comment('Customer-facing paragraph on the product page.');
            $table->text('internal_notes')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('product_id', 'product_halal_profiles_product_unique');
            $table->index(['overall_status', 'is_certified'], 'php_status_certified_index');
            $table->index('is_wudu_friendly', 'php_wudu_index');
        });
    }

    public function down(): void { Schema::dropIfExists('product_halal_profiles'); }
};
