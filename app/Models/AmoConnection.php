<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmoConnection extends Model
{
    public function user() {
        return $this->hasOne(User::class, 'amo_connection_id', 'id');
    }
}
