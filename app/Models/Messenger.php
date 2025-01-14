<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Messenger extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Указываем, какие атрибуты можно заполнять.
     */
    protected $fillable = [
        'name',
        'config',
    ];

    /**
     * Включаем мягкое удаление при разрешении маршрута.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    /**
     * Определяем связь с другой моделью (например, сообщения).
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Локальная область запроса для фильтрации.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%'.$search.'%');
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }
}
