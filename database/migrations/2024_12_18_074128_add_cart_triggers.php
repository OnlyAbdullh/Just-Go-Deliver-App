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
        // Trigger for Insert
        DB::unprepared('
            CREATE TRIGGER after_cart_product_insert
            AFTER INSERT ON cart_products
            FOR EACH ROW
            BEGIN
                DECLARE product_price DECIMAL(10, 2);

                -- Fetch the product price from the store_products table
                SELECT price INTO product_price
                FROM store_products
                WHERE id = NEW.store_product_id;

                -- Update the total_price in the carts table
                UPDATE carts
                SET total_price = total_price + (product_price * NEW.amount_needed)
                WHERE id = NEW.cart_id;
            END
        ');

        // Trigger for Update
        DB::unprepared('
            CREATE TRIGGER after_cart_product_update
            AFTER UPDATE ON cart_products
            FOR EACH ROW
            BEGIN
                DECLARE old_product_price DECIMAL(10, 2);
                DECLARE new_product_price DECIMAL(10, 2);

                -- Fetch the old and new product prices from the store_products table
                SELECT price INTO old_product_price
                FROM store_products
                WHERE id = OLD.store_product_id;

                SELECT price INTO new_product_price
                FROM store_products
                WHERE id = NEW.store_product_id;

                -- Adjust the total_price in the carts table
                UPDATE carts
                SET total_price = total_price - (old_product_price * OLD.amount_needed) + (new_product_price * NEW.amount_needed)
                WHERE id = NEW.cart_id;
            END
        ');

        // Trigger for Delete
        DB::unprepared('
            CREATE TRIGGER after_cart_product_delete
            AFTER DELETE ON cart_products
            FOR EACH ROW
            BEGIN
                DECLARE product_price DECIMAL(10, 2);

                -- Fetch the product price from the store_products table
                SELECT price INTO product_price
                FROM store_products
                WHERE id = OLD.store_product_id;

                -- Update the total_price in the carts table
                UPDATE carts
                SET total_price = total_price - (product_price * OLD.amount_needed)
                WHERE id = OLD.cart_id;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_cart_product_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS after_cart_product_update');
        DB::unprepared('DROP TRIGGER IF EXISTS after_cart_product_delete');
    }
};
