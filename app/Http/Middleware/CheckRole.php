<?php

declare(strict_types=1);

namespace App\Http\Middleware;

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

        // Iegūstam lomas un droši izvelkam tekstu, neatkarīgi no tā, 
        // vai tas ir Enum objekts vai parasts string
        $userRoles = $request->user()->roles->pluck('name')->map(function ($role) {
            // Ja $role ir Enum (kā RoleNameEnum), tad ņemam tā ->value
            $roleValue = $role instanceof \BackedEnum ? $role->value : $role;
            
            return strtolower((string) $roleValue);
        })->toArray();

        // Arī pieprasītās lomas (no web.php) pārveidojam uz mazajiem burtiem
        $requiredRoles = array_map('strtolower', $roles);

        foreach ($requiredRoles as $role) {
            if (in_array($role, $userRoles, true)) {
                return $next($request);
            }
        }

        abort(403, 'Jums nav piekļuves tiesību šai lapai.');
    }
}