<?php

namespace App\Listeners;

use App\Events\RoleRevoked;
use Illuminate\Support\Facades\Log;

class RoleRevokedListener
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\RoleRevoked  $event
     * @return void
     */
    public function handle(RoleRevoked $event)
    {
        Log::channel('security')->warning('Rol removido de usuario', [
            'user_id' => $event->model->id,
            'user_email' => $event->model->email ?? 'N/A',
            'role_name' => $event->role->name,
            'revoked_by' => auth()->id(),
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString()
        ]);
    }
}
