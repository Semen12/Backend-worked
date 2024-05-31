<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEmailVerificationCode extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
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
        $Code = str("<b>{$this->code}</b>");
        $time = 10;

        return (new MailMessage)
            ->subject('Уведомление о смене адреса электронной почты')
            ->line(str("Для подтверждения смены адреса электронной почты на текущий, пожалуйста, используйте следующий код
                     подтверждения: {$Code}")->toHtmlString())
                    //->action('Notification Action', url('/'))
            ->line('Код действителен в течение'.$time.' минут.')
            ->line('Если вы не запрашивали смену почты, можете просто проигнорировать это письмо.');

        // что-то например добавить еслт вы зареганы но получили письмо то сбросить пароль ?
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
