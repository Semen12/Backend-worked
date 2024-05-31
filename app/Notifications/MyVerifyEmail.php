<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class MyVerifyEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
       
            $urlFrontend = config('app.frontend_url').URL::signedRoute('verification.verify', ['id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification())], now()->addMinutes(60), false);
            $time = 60;

            return (new MailMessage)
                ->subject('Уведомление о подтверждении адреса электронной почты')
                ->line('Нажмите на кнопку ниже, чтобы подтвердить свою электронную почту.')
                ->action('Подтвердить адрес', $urlFrontend)
                ->line("Данная ссылка  доступна в течение {$time} минут")
                ->line('Если вы не создавали аккаунт, проигнорируйте данное письмо.');
       
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
