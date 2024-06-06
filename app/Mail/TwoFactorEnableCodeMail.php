<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorEnableCodeMail extends Mailable
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

    public function build(): TwoFactorEnableCodeMail
    {
        return $this->subject(' Подтверждение включения 2FA')
            ->view('emails.two_factor_enable_code')
            ->with(['confirmationCode' => $this->confirmationCode]);
    }
    /**
     * Get the message envelope.
     */
    /* public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Two Factor Enable Code Mail',
        );
    }
*/
    /**
     * Get the message content definition.
     */
    /*
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    } 
    */

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
