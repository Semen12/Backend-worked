<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\Factory as Queue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

class OldEmailVerificationCode extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
  //  protected string $code;

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
        $Code = str("<b>{$this->code}</b>" );

        $fontend= config('app.frontend_url').'/reset-password/';
        $url = str('<a href='.$fontend.'>'.'ссылке'.'</a>');
        return (new MailMessage)
                    ->subject('Уведомление о смене адреса электронной почты')
                  ->line(str("Для смены адреса электронной почты на новый, пожалуйста,
            используйте  код: {$Code} для подтверждения владения учетной записью: ")->toHtmlString())
                    ->line('Код действителен в течение 5 минут.')
                    ->line('Если вы не регистрировались  на нашем сайте, никаких действий не нужно.')
                    ->line(str("Если вы  регистрировались на нашем сайте, но не запращивали смену почты, то перейдите по {$url} ,
                    чтобы сбросить пароль.")->toHtmlString());

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
