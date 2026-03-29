<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->enum('movement_type', ['PURCHASE', 'SALE', 'ADJUSTMENT']);
            $table->integer('quantity');                    // always positive
            $table->decimal('unit_cost', 10, 2);
            $table->integer('remaining_quantity');          // FIFO running total
            $table->foreignId('purchase_item_id')
                  ->nullable()
                  ->constrained('purchase_items')
                  ->nullOnDelete();
            $table->foreignId('sale_item_id')
                  ->nullable()
                  ->constrained('sale_items')
                  ->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};