<?php

namespace App\Http\Middleware;

use App\Enums\Role\RoleNameEnum;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    private const AUTH = 'auth';
    private const FLASH = 'flash';

    private const USER = 'user';
    private const ID = 'id';
    private const NAME = 'name';
    private const EMAIL = 'email';
    private const ROLES = 'roles';
    private const PROFILE = 'profile';
    private const SUCCESS = 'success';
    private const ERROR = 'error';
    private const INFO = 'info';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            self::AUTH => [
                self::USER => $request->user() ? [
                    self::ID => $request->user()->id,
                    self::NAME => $request->user()->name,
                    self::EMAIL => $request->user()->email,
                    self::ROLES => $request->user()->roles->pluck(self::NAME)
                        ->map(fn ($r) => $r instanceof RoleNameEnum ? $r->value : (string) $r)
                        ->toArray(),
                    self::PROFILE => $request->user()->profile,
                ] : null,
            ],
            self::FLASH => [
                self::SUCCESS => fn () => $request->session()->get(self::SUCCESS),
                self::ERROR => fn () => $request->session()->get(self::ERROR),
                self::INFO => fn () => $request->session()->get(self::INFO),
            ],
        ];
    }
}