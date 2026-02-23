<?php

declare(strict_types=1);

namespace App\Services\Repositories\Auth;

use App\DataTransferObjects\Auth\LoginUserRequestData;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthLogicRepository
{
    public function login(LoginUserRequestData $data, string $ipAddress): void
    {
        $this->ensureIsNotRateLimited($data->email, $ipAddress);

        if (! Auth::attempt([
            User::EMAIL => $data->email,
            User::PASSWORD => $data->password,
        ], $data->remember)) {
            RateLimiter::hit($this->throttleKey($data->email, $ipAddress));

            throw ValidationException::withMessages([
                User::EMAIL => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($data->email, $ipAddress));
        
        session()->regenerate();
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    private function ensureIsNotRateLimited(string $email, string $ipAddress): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($email, $ipAddress), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($email, $ipAddress));

        throw ValidationException::withMessages([
            User::EMAIL => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function throttleKey(string $email, string $ipAddress): string
    {
        return Str::transliterate(Str::lower($email).'|'.$ipAddress);
    }
}