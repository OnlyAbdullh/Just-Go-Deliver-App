<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the trigger in raw SQL
        DB::unprepared('
            CREATE TRIGGER update_cart_products_on_store_update
            AFTER UPDATE ON store_products
            FOR EACH ROW
            BEGIN
                UPDATE cart_products
                SET price = NEW.price,
                    quantity = NEW.quantity,
                    description = NEW.description
                WHERE store_product_id = NEW.id;
            END;
        ');
    }

    public function down(): void
    {
        // Drop the trigger if the migration is rolled back
        DB::unprepared('DROP TRIGGER IF EXISTS update_cart_products_on_store_update');
    }
};
