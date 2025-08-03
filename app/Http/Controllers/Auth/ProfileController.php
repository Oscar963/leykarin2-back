<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\AuthResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\ProfileRequest;
use Illuminate\Validation\Rules\Password;
use App\Traits\LogsActivity;

class ProfileController extends Controller
{
    use LogsActivity;

    /**
     * Actualiza el perfil del usuario. 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(ProfileRequest $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'permissions']);
        $validated = $request->validated();

        $user->fill($validated); // Llena el modelo con los datos validados
        if ($user->isDirty()) {
            $user->save();
        }

        $this->logActivity('update_profile', 'Usuario actualizó su perfil');
        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => new AuthResource($user),
        ], 200);
    }

    /**
     * Cambia la contraseña del usuario.
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()],
        ]);

        $user = $request->user();
        $user->update(['password' => bcrypt($validated['password'])]);

        $this->logActivity('update_password', 'Usuario cambió su contraseña');
        return response()->json(['message' => 'Contraseña cambiada exitosamente']);
    }
}
