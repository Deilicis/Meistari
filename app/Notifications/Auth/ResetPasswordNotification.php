<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Paroles atjaunošana — Meistari')
            ->greeting('Sveiki!')
            ->line('Saņēmām pieprasījumu atjaunot jūsu paroli.')
            ->line('Šī saite ir derīga ' . config('auth.passwords.' . config('auth.defaults.passwords') . '.expire') . ' minūtes.')
            ->action('Atjaunot paroli', $url)
            ->line('Ja tu neprasīji paroles maiņu, vari ignorēt šo e-pastu — tava parole netiks mainīta.')
            ->salutation('Ar cieņu, Meistari komanda');
    }
}
