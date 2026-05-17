<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Habilita Sanctum SPA: cookies de sesión válidas para rutas /api/*
        $middleware->statefulApi();

        // En producción, Caddy actúa como reverse proxy desde 127.0.0.1.
        // Esto le dice a Laravel que confíe en los headers X-Forwarded-*
        // (Proto, Host, For) para reconocer que la URL pública es HTTPS.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'auth.session' => \App\Http\Middleware\RequireAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
