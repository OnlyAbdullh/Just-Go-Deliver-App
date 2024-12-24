<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order_Product extends Model
{
    use HasFactory;

    protected $table = 'order_products';

    protected $fillable = [
        'order_id',
        'store_product_id',
        'quantity',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function storeProduct()
    {
        return $this->belongsTo(Store_Product::class, 'store_product_id');
    }
}
