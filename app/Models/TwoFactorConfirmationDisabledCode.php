<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TwoFactorConfirmationDisabledCode extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'code', 'expires_at'];

    protected $hidden = ['code'];

    protected function casts(): array
    {
        return [

            'code' => 'hashed',
            'expires_at' => 'datetime',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }
}
