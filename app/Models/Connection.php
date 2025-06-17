<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    protected $fillable = [
        "phone",
        "account_id"
    ];

    public function user() {
        return $this->hasOne(User::class, 'connection_id', 'id');
    }
}
