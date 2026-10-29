<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminInvitation extends Notification
{
    public function __construct(private readonly string $inviter) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You have been invited to manage a DotBio')
            ->line("{$this->inviter} invited you to manage their DotBio.")
            ->line('Log in with any account (Discord, Google, email...) that uses this email address.')
            ->action('Accept the invitation', route('login'));
    }
}
