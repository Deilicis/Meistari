<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->profile) {
            return redirect()->route('profile.edit')->with('error', 'Lūdzu, vispirms izveidojiet profilu!');
        }

        $profile = $user->profile;

        if (empty($profile->phone) || empty($profile->city)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Lai turpinātu, lūdzu, norādiet savu pilsētu un telefona numuru!');
        }

        return $next($request);
    }
}