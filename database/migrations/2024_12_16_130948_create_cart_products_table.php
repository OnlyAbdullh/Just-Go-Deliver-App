<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_product_id')->constrained('store_products')->cascadeOnDelete();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->integer('amount_needed');
            $table->unique(['cart_id', 'store_product_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_products');
    }
};
