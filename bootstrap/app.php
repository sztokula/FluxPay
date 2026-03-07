<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->api(prepend: [
            \App\Http\Middleware\AttachCorrelationId::class,
            \App\Http\Middleware\EnsureIdempotency::class,
        ]);
        $middleware->redirectGuestsTo(function (Request $request): ?string {
            if ($request->expectsJson()) {
                return null;
            }

            return route('auth.login.form');
        });

        $middleware->alias([
            'idempotency' => \App\Http\Middleware\EnsureIdempotency::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'correlation_id' => $request->attributes->get('correlation_id'),
                ], 401);
            }

            return null;
        });
    })->create();
