<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenBlacklist extends Model
{
    use HasFactory;

    protected $fillable = ['token', 'expires_at'];

    public $timestamps = true;
}
