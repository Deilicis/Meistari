<?php

declare(strict_types=1);

namespace App\Notifications\Service;

use App\Models\ServiceApplication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewServiceApplicationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ServiceApplication $application,
        private readonly User $applicant,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $serviceTitle = $this->application->service->getTitle();
        $seekerName   = $this->applicant->getName();
        $message      = $this->application->getMessage();
        $budgetOffer  = $this->application->getBudgetOffer();

        $preview = mb_strlen($message) > 200
            ? mb_substr($message, 0, 200) . '…'
            : $message;

        $mail = (new MailMessage)
            ->subject('Jauns pieprasījums jūsu pakalpojumam')
            ->greeting('Sveiki!')
            ->line("**{$seekerName}** ir pieteicies jūsu pakalpojumam:")
            ->line("**{$serviceTitle}**")
            ->line("Ziņojums: *\"{$preview}\"*");

        if ($budgetOffer !== null) {
            $mail->line('Piedāvātais budžets: **' . number_format($budgetOffer, 2) . ' €**');
        }

        return $mail
            ->action('Skatīt manus pakalpojumus', route('master.services.index'))
            ->line('Sazināties ar klientu, izmantojot platformas ziņojumu sistēmu.')
            ->salutation('Ar cieņu, Meistari komanda');
    }
}
