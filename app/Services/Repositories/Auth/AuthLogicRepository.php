<?php

declare(strict_types=1);

namespace App\Services\Repositories\Auth;

use App\DataTransferObjects\Auth\ConfirmPasswordRequestData;
use App\DataTransferObjects\Auth\LoginUserRequestData;
use App\DataTransferObjects\Auth\ResetPasswordRequestData;
use App\DataTransferObjects\Auth\SendPasswordResetLinkRequestData;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthLogicRepository
{
    private const PASSWORD_CONFIRMATION = 'password_confirmation';
    private const TOKEN = 'token';

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

        $user = Auth::user();
        if ($user instanceof User && $user->isSuspended()) {
            Auth::logout();
            throw ValidationException::withMessages([
                User::EMAIL => 'Šis konts ir apturēts. Sazinies ar administrāciju.',
            ]);
        }

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

    public function sendPasswordResetLink(SendPasswordResetLinkRequestData $data): string
    {
        $status = Password::sendResetLink([
            User::EMAIL => $data->email
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                User::EMAIL => [trans($status)],
            ]);
        }

        return $status;
    }

    public function resetPassword(ResetPasswordRequestData $data): string
    {
        $status = Password::reset(
            [
                User::EMAIL => $data->email,
                User::PASSWORD => $data->password,
                self::PASSWORD_CONFIRMATION => $data->password,
                self::TOKEN => $data->token,
            ],
            function ($user, string $password) {
                $user->forceFill([
                    User::PASSWORD => Hash::make($password),
                    User::REMEMBER_TOKEN => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                User::EMAIL => [trans($status)],
            ]);
        }

        return $status;
    }

    public function confirmPassword(User $user, ConfirmPasswordRequestData $data): void
    {
        if (! Auth::guard('web')->validate([
            'email' => $user->email,
            'password' => $data->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session()->put('auth.password_confirmed_at', time());
    }

    public function sendEmailVerificationNotification(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->sendEmailVerificationNotification();
        
        return true;
    }

    public function verifyEmail(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return true;
        }

        return false;
    }
}