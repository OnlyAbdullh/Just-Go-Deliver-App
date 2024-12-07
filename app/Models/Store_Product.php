<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store_Product extends Model
{
    use HasFactory;

    protected $table = 'store__products';

    protected $fillable = [
        'store_id',
        'product_id',
        'price',
        'quantity',
        'description',
        'sold_quantity'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
