<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRefreshToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'refresh_token',
        'expires_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
