<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Store extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'logo', 'user_id', 'location', 'description'];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
