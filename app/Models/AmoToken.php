<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmoToken extends Model
{
    protected $fillable = [
        'token',
        'refresh_token',
        'expires_at',
    ];
}
