<?php

use App\Helper\ApiResponseHelper;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if ($e instanceof ValidationException) {
                    return ApiResponseHelper::validationError($e->validator)->send();
                }

                if ($e instanceof AuthenticationException) {
                    return ApiResponseHelper::unauthorized($e->getMessage() ?: 'No autenticado.')->send();
                }

                if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                    return ApiResponseHelper::notFound('Recurso')->send();
                }

                if ($e instanceof HttpExceptionInterface) {
                    return ApiResponseHelper::returnError($e->getMessage(), null)
                        ->setStatus($e->getStatusCode())
                        ->send();
                }

                return ApiResponseHelper::generalError($e)->send();
            }
        });
    })->create();
