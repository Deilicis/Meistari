<?php

declare(strict_types=1);

namespace App\Notifications\Application;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Application $application,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->application->jobRequest->getTitle();

        return (new MailMessage)
            ->subject('Jūsu pieteikums ir pieņemts!')
            ->greeting('Apsveicam!')
            ->line("Jūsu pieteikums darbam **{$jobTitle}** ir pieņemts.")
            ->line('Darba devējs izvēlējās tieši jūs. Sazināties ar darba devēju, izmantojot platformas ziņojumu sistēmu, lai vienotos par darba sākumu.')
            ->action('Skatīt darbu', route('jobs.show', $this->application->jobRequest->getId()))
            ->line('Paldies, ka izmantojat Meistari platformu!')
            ->salutation('Ar cieņu, Meistari komanda');
    }
}
