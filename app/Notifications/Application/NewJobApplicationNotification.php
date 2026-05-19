<?php

declare(strict_types=1);

namespace App\Notifications\Application;

use App\Models\Application;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewJobApplicationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Application $application,
        private readonly User $applicant,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->application->jobRequest->getTitle();
        $masterName = $this->applicant->getName();
        $priceOffer = $this->application->getPriceOffer();

        $message = (new MailMessage)
            ->subject('Jauns pieteikums jūsu sludinājumam')
            ->greeting('Sveiki!')
            ->line("**{$masterName}** ir iesniedzis pieteikumu jūsu sludinājumam:")
            ->line("**{$jobTitle}**");

        if ($priceOffer !== null) {
            $message->line('Piedāvātā cena: **' . number_format($priceOffer, 2) . ' €**');
        }

        return $message
            ->action('Skatīt darbu', route('jobs.show', $this->application->jobRequest->getId()))
            ->line('Piesakieties platformā, lai apskatītu un pieņemtu vai noraidītu pieteikumu.')
            ->salutation('Ar cieņu, Meistari komanda');
    }
}
