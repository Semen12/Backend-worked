<?php

namespace App\Providers;

use App\Http\Responses\CustomLogoutResponseFortify;


use App\Notifications\EmailVerificationCode;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(LogoutResponseContract::class, CustomLogoutResponseFortify::class);



    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {


        ResetPassword::toMailUsing(function (object $notifiable, string $token){
            $count = config('auth.passwords.expire');
            $urlFrontend = config('app.frontend_url')."/password-reset?email={$notifiable->getEmailForPasswordReset()}&token=$token";
            return (new MailMessage)
            ->subject('Уведомление о сбросе пароля')
            ->line('Вы получили это письмо, потому что мы получили запрос на сброс пароля для вашей учетной записи')
            ->action('Сбросить пароль',   $urlFrontend )
            ->line("Срок действия этой ссылки для сброса пароля истечет через  {$count} минут.")
            ->line('Если вы не запрашивали сброс пароля, то проигнорируйте это письмо.');
        });


    // подключение и кастомизация подтверждения почты: изменяем адрес для ссылки и текст письма, удаляем адрес по умолчанию, создаем свой
    // также добавляем необходимые параметры для роута verification.verify (id, hash, срок дейсвия, сигнатура)
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
                $urlFrontend=config('app.frontend_url').URL::signedRoute('verification.verify',['id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification())], now()->addMinutes(60), false);
                $time = 60;
           return (new MailMessage)
                ->subject('Уведомление о подтверждении адреса электронной почты')
                ->line('Нажмите на кнопку ниже, чтобы подтвердить свою электронную почту.')
                ->action('Подтвердить адрес', $urlFrontend)
                ->line("Данная ссылка  доступна в течение {$time} минут")
                ->line('Если вы не создавали аккаунт, проигнорируйте данное письмо.');
        });



    }
}
