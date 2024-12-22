<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRules;

class PasswordResetController extends Controller
{
    /**
     * Restablecer la contraseña.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request)
    {
        $request->validate(
            [
                'token' => 'required|string',
                'email' => 'required|email',
                'password' => ['required', PasswordRules::defaults()],
            ]
        );

        $status = Password::reset(
            $request->only('email', 'password', 'token'),
            function ($user) use ($request) {
                $user->password = bcrypt($request->password);
                $user->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['status' => '¡La contraseña ha sido restablecida correctamente!'], 200);
        }

        switch ($status) {
            case Password::INVALID_TOKEN:
                return response()->json(['error' => 'El token de restablecimiento ha expirado o es inválido.'], 400);

            case Password::INVALID_USER:
                return response()->json(['error' => 'No se ha encontrado un usuario con esa dirección de correo electrónico.'], 400);

            default:
                return response()->json(['error' => 'No se pudo restablecer la contraseña. Inténtalo nuevamente más tarde.'], 400);
        }
    }
}
