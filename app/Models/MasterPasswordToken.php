<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPasswordToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'expired_at',

    ];

    protected $hidden = [
        'token',
    ];

    protected function casts(): array // добавления атрибутов
    {
        return [
            'token' => 'hashed',
            'expired_at' => 'datetime',

        ];
    }
    // Добавляем отношение к модели User
    public function user()
    {
        return $this->belongsTo(User::class); // обратное отношение один к одному
    }
    // так как название модели написано по типу CameCase, то установить какую талицу использовать
    public function getTable()
    {
        return 'master_password_reset_tokens';
    }

//    если будет автоматом запускаться удаление токенов после истечения срока действия
//    public static function updateExpiredCodesStatus(): void
//    {
//        self::where('expired_at', '<', now())->update(['status' => 'invalid']);
//    }
}
