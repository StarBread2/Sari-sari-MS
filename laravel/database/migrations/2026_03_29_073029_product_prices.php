<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('selling_price', 10, 2);
            $table->dateTime('effective_to');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['product_id', 'effective_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};