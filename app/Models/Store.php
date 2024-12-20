<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'logo',
        'user_id',
        'location_ar',
        'location_en',
        'description_ar',
        'description_en',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'store_products')
            ->withPivot('price', 'quantity', 'description_ar', 'description_en', 'sold_quantity') // Corrected 'quantity'
            ->withTimestamps();
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
