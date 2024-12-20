<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_ar',
        'category_id'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_products')
            ->withPivot('price', 'quantity', 'description_ar','description_en', 'sold_quantity')
            ->withTimestamps();
    }
    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
}
