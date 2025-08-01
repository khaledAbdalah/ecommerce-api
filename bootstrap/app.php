<?php

use App\Http\Middleware\GuestApiRequest;
use App\Http\Middleware\IsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api.guest' => GuestApiRequest::class,
            'isAdmin' => IsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, $request) {
            if ( $request->wantsJson() ) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ( $request->wantsJson() ) {
                $previous = $e->getPrevious();
                if ( $previous instanceof ValidationException ) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Model not found'
                    ], 404);
                }

                return response()->json([
                    'success' => false,
                    'error' => 'Not found',
                ], 404);
            }

        });

    })->create();