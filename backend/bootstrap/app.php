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
        // Removed $middleware->statefulApi(); to disable CSRF for API routes
        // as frontend and backend are on different origins for token-based auth.
        // The 'api' middleware group is stateless by default and does not include CSRF protection.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
