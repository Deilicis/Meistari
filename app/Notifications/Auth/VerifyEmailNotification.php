<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Apstiprini savu e-pasta adresi — Meistari')
            ->greeting('Sveiki!')
            ->line('Paldies, ka reģistrējāties Meistari platformā!')
            ->line('Nospiediet pogu zemāk, lai apstiprinātu savu e-pasta adresi.')
            ->action('Apstiprināt e-pastu', $url)
            ->line('Ja tu nereģistrējies, vari ignorēt šo e-pastu.')
            ->salutation('Ar cieņu, Meistari komanda');
    }
}
