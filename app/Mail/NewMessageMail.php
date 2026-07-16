<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $senderName;
    public string $body;
    public string $actionUrl;
    public string $actionLabel;

    public function __construct(string $recipientName, string $senderName, string $body, string $actionUrl, string $actionLabel = 'Open Conversation')
    {
        $this->recipientName = $recipientName;
        $this->senderName    = $senderName;
        $this->body          = $body;
        $this->actionUrl     = $actionUrl;
        $this->actionLabel   = $actionLabel;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New message from ' . $this->senderName . ' — UCC IT Center Services',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-message',
        );
    }
}