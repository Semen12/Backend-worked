<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordLink extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected string $token;
    public function __construct($token)
    {
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
        $count = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
        $urlFrontend = config('app.frontend_url')."/password-reset?email={$notifiable->getEmailForPasswordReset()}&token={$this->token}";

        return (new MailMessage)
            ->subject('Уведомление о сбросе пароля')
            ->line('Вы получили это письмо, потому что мы получили запрос на сброс пароля для вашей учетной записи')
            ->action('Сбросить пароль', $urlFrontend)
            ->line("Срок действия этой ссылки для сброса пароля истечет через {$count} минут.")
            ->line('Если вы не запрашивали сброс пароля, то проигнорируйте это письмо.');
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
