<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationСode extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'type',
        'code',
        'verification_value',
    ];

    protected function casts(): array // добавления атрибутов
    {
        return [
            'code'=>'hashed',
            'verified_at' => 'datetime',
            'expired_at' => 'datetime',

        ];
    }
    // Добавляем отношение к модели User
    public function user()
    {
        return $this->belongsTo(User::class); // обратное отношение один к одному
    }
}
