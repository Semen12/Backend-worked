<?php

namespace App\Models;

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
            'type' => AccountType::class,
            'password' => 'encrypted',

        ];
    }

    // Добавляем отношение к модели User
    public function user()
    {
        return $this->belongsTo(User::class); // обратное отношение один к одному
    }

    public static function getResponseCode($url): bool
    {
        $header = '';
        $options = [
            CURLOPT_URL => trim($url),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        curl_exec($ch);
        if (! curl_errno($ch)) {
            $header = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        curl_close($ch);

        if ($header > 0 && $header < 400) {
            return true;
        } else {
            return false;
        }
    }
}
