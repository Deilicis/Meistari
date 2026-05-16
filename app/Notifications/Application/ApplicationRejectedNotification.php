<?php

declare(strict_types=1);

namespace App\Notifications\Application;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationRejectedNotification extends Notification
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
            ->subject('Atjauninājums par jūsu pieteikumu')
            ->greeting('Sveiki!')
            ->line("Diemžēl jūsu pieteikums darbam **{$jobTitle}** šoreiz netika pieņemts.")
            ->line('Nepadodies - Meistari platformā ir daudz citu iespēju, kas gaida tieši jūs.')
            ->action('Pārlūkot sludinājumus', route('master.job-requests.index'))
            ->salutation('Ar cieņu, Meistari komanda');
    }
}
