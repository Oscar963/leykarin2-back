<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'status' => 401,
            'error' => [
                'message' => 'Unauthenticated.'
            ]
        ], 401);
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof AuthorizationException) {
            return response()->json([
                'status' => 403,
                'error' => [
                    'message' => 'Forbidden.'
                ]
            ], 403);
        }

        if ($exception instanceof ThrottleRequestsException) {
            return response()->json([
                'status' => 429,
                'error' => [
                    'message' => 'Demasiados intentos. Por favor, inténtalo de nuevo más tarde.'
                ]
            ], 429);
        }

        // ...otros errores...

        return parent::render($request, $exception);
    }
}
