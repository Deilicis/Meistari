<?php

namespace App\Http\Middleware;

use App\Enums\Role\RoleNameEnum;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    private const AUTH         = 'auth';
    private const FLASH        = 'flash';
    private const USER         = 'user';
    private const ID           = 'id';
    private const NAME         = 'name';
    private const EMAIL        = 'email';
    private const ROLES        = 'roles';
    private const PROFILE      = 'profile';
    private const ACTIVE_ROLE  = 'active_role';
    private const IS_MASTER    = 'is_master';
    private const IS_SEEKER    = 'is_seeker';
    private const HAS_BOTH     = 'has_both_roles';
    private const SUCCESS      = 'success';
    private const ERROR        = 'error';
    private const INFO         = 'info';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        // Load roles once so all helpers (isMaster, isSeeker, hasBothRoles) use the same collection
        if ($user) {
            $user->load('roles');
        }

        return [
            ...parent::share($request),
            self::AUTH => [
                self::USER => $user ? [
                    self::ID          => $user->id,
                    self::NAME        => $user->name,
                    self::EMAIL       => $user->email,
                    self::ROLES       => $user->roles->pluck(self::NAME)
                        ->map(fn ($r) => $r instanceof RoleNameEnum ? $r->value : (string) $r)
                        ->toArray(),
                    self::PROFILE     => $user->profile,
                    self::ACTIVE_ROLE => $user->getActiveRole()?->value,
                    self::IS_MASTER   => $user->isMaster(),
                    self::IS_SEEKER   => $user->isSeeker(),
                    self::HAS_BOTH    => $user->hasBothRoles(),
                ] : null,
            ],
            self::FLASH => [
                self::SUCCESS => fn () => $request->session()->get(self::SUCCESS),
                self::ERROR   => fn () => $request->session()->get(self::ERROR),
                self::INFO    => fn () => $request->session()->get(self::INFO),
            ],
        ];
    }
}
