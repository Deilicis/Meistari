<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Constants\ErrorMessages;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    private const FLASH_ERROR = 'error';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->profile) {
            return redirect()->route('profile.edit')->with(self::FLASH_ERROR, ErrorMessages::PROFILE_NOT_FOUND);
        }

        $profile = $user->profile;

        if (empty($profile->phone) || empty($profile->city)) {
            return redirect()->route('profile.edit')
                ->with(self::FLASH_ERROR, ErrorMessages::PROFILE_INCOMPLETE);
        }

        return $next($request);
    }
}
