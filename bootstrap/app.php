<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\App\Exceptions\Jobs\InvalidJobTransitionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        });
        $exceptions->render(function (\App\Exceptions\Proposals\InvalidProposalActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        });
        $exceptions->render(function (\App\Exceptions\Proposals\ProposalAuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        });
        $exceptions->render(function (\App\Exceptions\Proposals\ApplicationNotNegotiableException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        });
        $exceptions->render(function (\App\Exceptions\Categories\CategoryNotDeletableException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        });
        $exceptions->render(function (\App\Exceptions\Categories\CategoryMergeConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        });
        $exceptions->render(function (\App\Exceptions\Categories\SystemCategoryProtectedException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        });
    })->create();