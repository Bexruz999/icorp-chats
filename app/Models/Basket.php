<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Basket extends Model
{
    public function items(): HasMany
    {
        return $this->hasMany(BasketItem::class);
    }
}
