<?php

namespace App\Models;

use App\Notifications\MyVerifyEmail;
use App\Notifications\ResetPasswordLink;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'master_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'master_password',
        'remember_token',
        // добавить для скрытия полей 2FA 'two_factor_recovery_codes',
        //    'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'master_password' => 'hashed',
        ];
    }

    public function sendEmailVerificationNotification() //использрвание кастомной нотификации для отправки уведомления для подтверждения почты
    {
        $this->notify(new MyVerifyEmail);
    }
    /**
     * Отправить пользователю уведомление о сбросе пароля.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void //кастомная нотификация для отправки ссылки восстановления пароля
    {
        $this->notify(new ResetPasswordLink($token));
    }
    public function accounts()
    {
        return $this->hasMany(Account::class); // установление связи один ко многим
    }

    public function verificationCodes()
    {
        return $this->hasMany(VerificationCode::class);
    }

    public function masterPasswordToken() //
    {
        return $this->hasOne(MasterPasswordToken::class); // один к одному
    }

    public function twoFactorDisabledCodes()
    {
        return $this->hasOne(TwoFactorConfirmationDisabledCode::class);
    }

    public function TwoFactorEnableCode()
    {
        return $this->hasOne(TwoFactorEnableCode::class);
    }

    public function  CodeDestroyUserAccount()
    {
        return $this->hasOne(VerificationCodeDestroyUser::class);
    }
}
