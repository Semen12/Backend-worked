<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CodeDestroyUser extends Mailable
{
    use Queueable, SerializesModels;

    protected string $code;

    /**
     * Create a new message instance.
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build(): CodeDestroyUser
    {
        return $this->view('emails.delete-account-confirmation')
            ->subject('Подтверждение удаления аккаунта')
            ->with(['code' => $this->code]);
    }

    /**
     * Get the message envelope.
     */
    /*  public function envelope(): Envelope
      {
          return new Envelope(
              subject: 'Code Destroy User',
          );
      }

      /**
       * Get the message content definition.
       */
    /*public function content(): Content
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
