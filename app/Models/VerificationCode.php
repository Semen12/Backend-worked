<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type_email',
        'code',
        'verification_value',
        'status',
        'expired_at',
        'verified_at',

    ];

    protected function casts(): array // добавления атрибутов
    {
        return [
            'code' => 'hashed',
            'verified_at' => 'datetime',
            'expired_at' => 'datetime',

        ];
    }

    // Добавляем отношение к модели User
    public function user()
    {
        return $this->belongsTo(User::class); // обратное отношение один к одному
    }

    // так как название модели написано по типу СameCase, то установить какую талицу использовать
    public function getTable()
    {
        return 'verification_codes';
    }
    public static  function customRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; // символы, которые могут быть использованы
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
    public static function updateExpiredCodesStatus(): void
    {
        self::where('expired_at', '<', now())->where('status', 'pending')->update(['status' => 'invalid']);
        self::where('status', 'invalid')->delete(); // удаляем все невалидные коды
    }
}
