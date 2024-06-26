<?php

namespace App\Models;

use App\Casts\EncryptedAccountTypeCast;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'url', 'name', 'login', 'password', 'description',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array // добавления атрибутов
    {
        return [
            'type' =>  EncryptedAccountTypeCast::class,
            'login' => 'encrypted',
            'url' => 'encrypted',
            'description' => 'encrypted',
            'name' => 'encrypted',
            'password' => 'encrypted',
        ];
    }

    // Добавляем отношение к модели User
    public function user()
    {
        return $this->belongsTo(User::class); // обратное отношение один к одному
    }

   
}
