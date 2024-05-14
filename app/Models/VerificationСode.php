<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class VerificationСode extends Model
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
    public static function updateExpiredCodesStatus(): void
    {
        self::where('expired_at', '<', now())->update(['status' => 'invalid']);
    }

}
