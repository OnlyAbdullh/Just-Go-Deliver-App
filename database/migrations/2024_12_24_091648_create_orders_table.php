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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_reference')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'delivered'])->default('pending');
            $table->date('order_date');
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
