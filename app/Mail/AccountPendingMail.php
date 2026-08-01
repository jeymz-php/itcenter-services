<?php
namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountPendingMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $dashboardUrl;

    public function __construct(User $user, string $dashboardUrl)
    {
        $this->user = $user;
        $this->dashboardUrl = $dashboardUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Registration Pending — UCC IT Center Services',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-pending',
        );
    }
}
