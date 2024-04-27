<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'source_type',
        'username',
        'password',
        'comment',
    ];

    protected function casts(): array // добавления атрибутов
    {
        return [
            'password' => 'hashed',
        ];
    }
    // Добавляем отношение к модели User
    public function user()
    {
        return $this->belongsTo(User::class); // обратное отношение один к одному
    }

}
