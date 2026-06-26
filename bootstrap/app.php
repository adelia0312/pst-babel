<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aktifkan CORS untuk Flutter Web / browser iPhone
        $middleware->use([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Jika CSRF token expired → balik ke form, jangan ke landing page
        $exceptions->render(function (TokenMismatchException $e, $request) {
            return redirect()->back()
                ->withInput($request->except('_token'))
                ->withErrors([
                    'csrf' => 'Sesi Anda telah berakhir. Silakan kirim ulang.',
                ]);
        });

        // Jika session habis / belum login → arahkan ke /login, bukan ke /
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect('/login')->withErrors([
                'auth' => 'Sesi Anda telah berakhir, silakan login kembali.',
            ]);
        });

    })->create();