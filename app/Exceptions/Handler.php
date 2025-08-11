<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use App\Exceptions\InvalidCredentialsException;
use Throwable;

class Handler extends ExceptionHandler
{
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
        // Se aplica esta lógica solo si la petición espera una respuesta JSON (típicamente APIs)
        $this->renderable(function (Throwable $e, $request) {
            if ($request->wantsJson()) {
                return $this->handleApiException($request, $e);
            }
        });
    }

    /**
     * Maneja las excepciones para las peticiones de API.
     */
    protected function handleApiException($request, Throwable $exception)
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof AuthenticationException) {
            return $this->jsonResponse('No autenticado.', 401);
        }

        if ($exception instanceof InvalidCredentialsException) {
            return $this->jsonResponse(
                'Credenciales incorrectas. Verifique su rut y contraseña e intente nuevamente.',
                401
            );
        }

        if ($exception instanceof AuthorizationException) {
            return $this->jsonResponse('No tienes permiso para realizar esta acción.', 403);
        }

        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            return $this->jsonResponse('El recurso solicitado no fue encontrado.', 404);
        }

        if ($exception instanceof ValidationException) {
            return $this->jsonResponse(
                'Los datos proporcionados no son válidos.',
                422,
                ['details' => $exception->errors()]
            );
        }

        if ($exception instanceof ThrottleRequestsException) {
            return $this->jsonResponse('Demasiados intentos. Por favor, inténtalo de nuevo más tarde.', 429);
        }

        if (
            $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException
            && $exception->getStatusCode() === 423
        ) {
            return $this->jsonResponse(
                'Se requiere verificación adicional para completar la autenticación.',
                423,
                ['two_factors' => true]
            );
        }

        if ($exception instanceof ExcelValidationException) {
            return $this->jsonResponse(
                'El archivo Excel no cumple con el formato requerido.',
                422,
                ['details' => $exception->errors()]
            );
        }

        // Para cualquier otra excepción, se devuelve un error 500
        $message = config('app.debug')
            ? $exception->getMessage()
            : 'Error interno del servidor.';

        return $this->jsonResponse($message, 500);
    }

    /**
     * Helper para crear una respuesta JSON estandarizada.
     */
    protected function jsonResponse(string $message, int $statusCode, array $data = [])
    {
        $response = [
            'status' => $statusCode,
            'error' => [
                'message' => $message,
            ]
        ];

        if (!empty($data)) {
            $response['error'] = array_merge($response['error'], $data);
        }

        return response()->json($response, $statusCode);
    }
}
