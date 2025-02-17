<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'order_reference', 'total_price', 'order_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function storeProducts()
    {
        return $this->belongsToMany(Store_Product::class, 'order_products')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    public function orderProducts()
    {
        return $this->hasMany(Order_Product::class);
    }
}
