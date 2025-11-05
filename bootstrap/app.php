<?php

use Illuminate\Auth\AuthenticationException;
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
    ->withMiddleware(function (Middleware $middleware) {
        // Your global middlewares if needed
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $exception, $request) {
            // Handle CSRF token mismatch
            if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['error' => 'CSRF token mismatch.'], 419);
                }
                
                // Redirect back with error for web requests
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Your session has expired. Please try again.']);
            }

            // Handle authentication exceptions
            if ($exception instanceof AuthenticationException) {
                // Check if the request is from API
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['error' => 'Unauthenticated'], 401);
                }
                
                // redirect to the login page (default Laravel behavior)
                return redirect()->guest(route('login'));
            }

            // Only return JSON for API requests or AJAX requests expecting JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine()
                ], 500);
            }

            // For web requests, let Laravel handle it normally (shows proper error page)
            return null;
        });
    })
    ->create();
    