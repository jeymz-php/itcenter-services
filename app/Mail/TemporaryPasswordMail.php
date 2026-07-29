<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TemporaryPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $temporaryPassword;
    public string $loginUrl;

    public function __construct(string $recipientName, string $temporaryPassword, string $loginUrl)
    {
        $this->recipientName     = $recipientName;
        $this->temporaryPassword = $temporaryPassword;
        $this->loginUrl          = $loginUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Temporary Password — UCC IT Center Services',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.temporary-password',
        );
    }
}