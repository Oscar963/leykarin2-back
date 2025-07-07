<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthService
{
    /**
     * Intenta autenticar a un usuario
     *
     * @param array $credentials
     * @param bool $remember
     * @return array
     */
    public function attemptLogin(array $credentials, bool $remember = false): array
    {
        try {
            if (!Auth::attempt($credentials, $remember)) {
                return [
                    'success' => false,
                    'message' => 'Credenciales incorrectas. Verifique su rut y contraseña e intente nuevamente.',
                    'status' => 401
                ];
            }

            $user = Auth::user();

            if (!$user->status) {
                Auth::logout();
                return [
                    'success' => false,
                    'message' => 'Tu cuenta está suspendida. Por favor contáctate con el administrador del sistema.',
                    'status' => 403
                ];
            }

            return [
                'success' => true,
                'user' => $user,
                'status' => 200
            ];
        } catch (\Exception $e) {
            Log::error('Error en autenticación: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cierra la sesión del usuario
     *
     * @return void
     */
    public function logout(): void
    {
        Auth::logout();
    }

    /**
     * Obtiene el usuario autenticado con sus relaciones
     *
     * @return User
     */
    public function getAuthenticatedUser(): User
    {
        return User::findOrFail(Auth::id());
    }
}
