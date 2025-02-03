<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bot extends Model
{

    protected $fillable = [
        'name',
        'token',
        'slug',
        'account_id'
    ];
    public function shops(): HasOne {
        return $this->hasOne(Shop::class);
    }
}
