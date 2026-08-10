<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cross-links between ingredient pages — "see also: cochineal, shellac".
        // Internal linking is what makes a 20-page content cluster rank as a cluster
        // rather than as 20 unrelated orphans.
        Schema::create('ingredient_related', function (Blueprint $table) {
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('related_ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);

            $table->primary(['ingredient_id', 'related_ingredient_id'], 'ingredient_related_primary');
            $table->index('related_ingredient_id', 'ingredient_related_reverse_index');
        });
    }

    public function down(): void { Schema::dropIfExists('ingredient_related'); }
};
