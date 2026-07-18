<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Exceptions\ShortCodeGenerationException;
use App\Exceptions\UrlAlreadyShortenedException;
use App\Exceptions\UrlNotFoundException;
use App\Support\ProblemDetails;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($exception instanceof ValidationException) {
                return ProblemDetails::response(
                    $request,
                    422,
                    'urn:test-spot:validation-error',
                    'Validation failed',
                    'The request contains invalid data.',
                    ['errors' => $exception->errors()],
                );
            }

            if ($exception instanceof UrlAlreadyShortenedException) {
                return ProblemDetails::response(
                    $request,
                    409,
                    'urn:test-spot:url-already-shortened',
                    'URL already shortened',
                    'The URL has already been shortened.',
                );
            }

            if ($exception instanceof UrlNotFoundException) {
                return ProblemDetails::response(
                    $request,
                    404,
                    'urn:test-spot:url-not-found',
                    'URL not found',
                    'The requested shortened URL was not found.',
                );
            }

            if ($exception instanceof ShortCodeGenerationException) {
                return ProblemDetails::response(
                    $request,
                    500,
                    'urn:test-spot:internal-error',
                    'Internal server error',
                    'The request could not be completed.',
                );
            }

            if ($exception instanceof HttpExceptionInterface) {
                $status = $exception->getStatusCode();

                return ProblemDetails::response(
                    $request,
                    $status,
                    'urn:test-spot:http-error',
                    'HTTP error',
                    $status === 404 ? 'The requested resource was not found.' : 'The request could not be completed.',
                );
            }

            return ProblemDetails::response(
                $request,
                500,
                'urn:test-spot:internal-error',
                'Internal server error',
                'The request could not be completed.',
            );
        });
    })->create();
