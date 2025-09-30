<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        // Detectar cambios en roles
        if ($user->wasChanged()) {
            $changes = $user->getChanges();
            $original = $user->getOriginal();
            
            // Loguear cambios importantes
            $sensitiveFields = ['status', 'email', 'rut', 'type_dependency_id'];
            
            foreach ($sensitiveFields as $field) {
                if (array_key_exists($field, $changes)) {
                    Log::channel('security')->info('Usuario modificado', [
                        'user_id' => $user->id,
                        'field' => $field,
                        'old_value' => $this->maskSensitiveData($field, $original[$field] ?? null),
                        'new_value' => $this->maskSensitiveData($field, $changes[$field]),
                        'modified_by' => auth()->id(),
                        'ip' => request()->ip(),
                        'timestamp' => now()->toISOString()
                    ]);
                }
            }
        }
        
        // Loguear cambios en roles (requiere verificación manual ya que roles es relación)
        // Esto se maneja mejor con eventos de Spatie Permission
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        Log::channel('security')->warning('Usuario eliminado', [
            'user_id' => $user->id,
            'email' => $user->email,
            'deleted_by' => auth()->id(),
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Handle the User "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        Log::channel('security')->info('Usuario restaurado', [
            'user_id' => $user->id,
            'email' => $user->email,
            'restored_by' => auth()->id(),
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Enmascara datos sensibles para logging.
     *
     * @param string $field
     * @param mixed $value
     * @return mixed
     */
    private function maskSensitiveData(string $field, $value)
    {
        if (in_array($field, ['email', 'rut']) && !empty($value)) {
            // Enmascarar parcialmente
            if ($field === 'email') {
                $parts = explode('@', $value);
                return substr($parts[0], 0, 3) . '***@' . ($parts[1] ?? '');
            }
            if ($field === 'rut') {
                return substr($value, 0, 3) . '***';
            }
        }
        
        return $value;
    }
}
