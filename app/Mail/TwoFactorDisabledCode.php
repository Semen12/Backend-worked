<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorDisabledCode extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  string  $confirmationCode
     * @return void
     */
    protected string $confirmationCode;

    public function __construct($confirmationCode)
    {
        $this->confirmationCode = $confirmationCode;
    }

    /**
     * Get the message envelope.
     */
    public function build(): TwoFactorDisabledCode
    {
        return $this->subject(' Отключения двухфакторной аутентификации')
            ->view('emails.confirmation_code')
            ->with(['confirmationCode' => $this->confirmationCode]);
    }
//    public function envelope(): Envelope
//    {
//        return new Envelope(
//            subject: ' Отключения двухфакторной аутентификации',
//
//        );
//    }
//
//
//    /**
//     * Get the message content definition.
//     */
//    public function content(): Content
//    {
//        return new Content(
//            view: 'emails.confirmation_code',
//        );
//    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
