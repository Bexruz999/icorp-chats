<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMessage extends Model
{
    public $timestamps = false;

    // Qo'shildi: ustunlarni aniqlash uchun
    protected $fillable = [
        'message_id',
        'chat_id',
        // ... boshqa ustunlar bo'lsa shu yerga qo'shing ...
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
