<?php
namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $loginUrl;

    public function __construct(User $user, string $loginUrl)
    {
        $this->user = $user;
        $this->loginUrl = $loginUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Approved — UCC IT Center Services',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-approved',
        );
    }
}
