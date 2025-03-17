<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Enviar el enlace de restablecimiento de la contraseña.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Verificar si el usuario existe en el sistema
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'El correo electrónico ingresado no está registrado en nuestro sistema. Por favor, verifique e intente nuevamente.'
            ], 404);
        }

        $key = Str::lower($request->email) . '|' . $request->ip();
        $maxAttempts = 5; // Número máximo de intentos permitidos
        $decayMinutes = 1; // Tiempo de espera antes de permitir nuevos intentos

        // Verificar si el límite de intentos ha sido alcanzado
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'Ha superado el número máximo de intentos. Por favor, intente nuevamente en ' . ceil($seconds / 60) . ' minutos.'
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => '¡El enlace de restablecimiento de contraseña ha sido enviado a su correo electrónico! Por favor, revise su bandeja de entrada.'
            ], 200);
        }

        return response()->json([
            'message' => 'Ocurrió un error al enviar el enlace de restablecimiento. Intente nuevamente más tarde.'
        ], 400);
    }
}
