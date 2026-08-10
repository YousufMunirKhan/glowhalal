<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_delta')->comment('Signed. -2 = two units left stock.');
            $table->integer('quantity_after')->comment('Running balance snapshot for audit.');
            $table->string('reason', 32)
                  ->comment('App\Enums\StockMovementReason: purchase|sale|return|adjustment|damage|recount|reservation_release');
            $table->nullableMorphs('reference');   // Order, StockReservation, manual adjustment
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['inventory_item_id', 'created_at'], 'inventory_movements_item_created_index');
            $table->index('reason', 'inventory_movements_reason_index');
        });
    }

    public function down(): void { Schema::dropIfExists('inventory_movements'); }
};
