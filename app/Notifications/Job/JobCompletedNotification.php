<?php

declare(strict_types=1);

namespace App\Notifications\Job;

use App\Models\JobRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly JobRequest $jobRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->jobRequest->getTitle();

        return (new MailMessage)
            ->subject('Darbs atzīmēts kā pabeigts')
            ->greeting('Paldies par labi padarītu darbu!')
            ->line("Darba devējs ir atzīmējis darbu **{$jobTitle}** kā pabeigtu.")
            ->line('Atstājiet atsauksmi par sadarbību — tas palīdz citiem klientiem atrast uzticamus meistarus.')
            ->action('Skatīt manus pieteikumus', route('master.applications.index'))
            ->salutation('Ar cieņu, Meistari komanda');
    }
}
