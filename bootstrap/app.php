<?php

use App\Exceptions\EmailNotVerifiedException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Unverified email on login — frontend uses require_verification to show OTP screen
        $exceptions->render(function (EmailNotVerifiedException $e) {
            return response()->json([
                'status'               => 0,
                'message'              => $e->getMessage(),
                'require_verification' => true,
            ], 403);
        });

        // Validation errors (422)
        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'status'  => 0,
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        });

        // Unauthenticated (401)
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Unauthenticated.',
            ], 401);
        });

        // Route not found (404)
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Route not found.',
            ], 404);
        });

    })->create();
