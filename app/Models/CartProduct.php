<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartProduct extends Model
{
    use HasFactory;

    public function storeProduct()
    {
        return $this->belongsTo(Store_Product::class, 'store_product_id');
    }
    
}
