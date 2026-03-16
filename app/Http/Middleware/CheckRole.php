<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Constants\ErrorMessages;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            abort(401);
        }

        $userRoles = $request->user()->roles->pluck('name')->map(function ($role) {
            $roleValue = $role instanceof \BackedEnum ? $role->value : $role;

            return strtolower((string) $roleValue);
        })->toArray();

        $requiredRoles = array_map('strtolower', $roles);

        foreach ($requiredRoles as $role) {
            if (in_array($role, $userRoles, true)) {
                return $next($request);
            }
        }

        abort(403, ErrorMessages::ACCESS_FORBIDDEN);
    }
}
