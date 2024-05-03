<?php

namespace App\Providers;

use App\Http\Responses\CustomLogoutResponseFortify;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
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
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
    // подключение и кастомизация подтверждения почты: изменяем адрес для ссылки и текст письма, удаляем адрес по умолчанию, создаем свой 
    // также добавляем необходимые параметры для роута verification.verify (id, hash, срок дейсвия, сигнатура)
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {   
                $urlFrontend=config('app.frontend_url').URL::signedRoute('verification.verify',['id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification())], now()->addMinutes(60), false);      
           return (new MailMessage)
                ->subject('Подтверждение адреса электронной почты')
                ->line('Нажмите на кнопку ниже, чтобы подтвердить свою электронную почту.')
                ->action('Подтвердить адрес', $urlFrontend) 
                ->line('Если вы не создавали аккаунт, проигнорируйте данное письмо.');
        });

      
    }
}
