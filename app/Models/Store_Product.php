<?php

namespace App\Models;

use DragonCode\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store_Product extends Model
{
    use HasFactory;

    protected $table = 'store_products';

    protected $fillable = [
        'store_id',
        'product_id',
        'price',
        'quantity',
        'description_en',
        'description_ar',
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

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'product_id', 'product_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'product_id', 'product_id');
    }
}
