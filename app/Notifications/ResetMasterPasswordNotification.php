<?php

namespace App\Notifications;

use AllowDynamicProperties;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

#[AllowDynamicProperties] class ResetMasterPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected string $token;

    public function __construct($token)
    {
        //
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $frontend = config('app.frontend_url').'/reset-password/';
        $url = str('<a href='.$frontend.'>'.'ссылке'.'</a>');

        $urlFrontend = config('app.frontend_url').URL::signedRoute('reset.master.password', ['id' => $notifiable->getKey(),
            'token' => $this->token], Carbon::now()->addMinutes(60), false);
        $time = 60;

        return (new MailMessage)
            ->subject('Уведомление о сбросе мастер-пароля')
            ->line('Нажмите на кнопку ниже, чтобы перейти к сбросу мастер-пароля.')
            ->action('Сбросить мастер-пароль', $urlFrontend)
            ->line("Данная ссылка  доступна в течение {$time} минут")
            ->line(str("Если вы  регистрировались на нашем сайте, но не запрашивали смену мастер-пароля, то перейдите по {$url} ,
                    чтобы сбросить пароль от учётной записи.")->toHtmlString());
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
