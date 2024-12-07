<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'main_image'
    ];

    public function category()
    {
        return $this->hasOne(Category::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_product')
            ->withPivot('price', 'quantity', 'description', 'sold_quantity')
            ->withTimestamps();
    }
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

}
