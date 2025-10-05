<?php

namespace App\Listeners;

use App\Events\RoleAssigned;
use Illuminate\Support\Facades\Log;

class RoleAssignedListener
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\RoleAssigned  $event
     * @return void
     */
    public function handle(RoleAssigned $event)
    {
        Log::channel('security')->info('Rol asignado a usuario', [
            'user_id' => $event->model->id,
            'user_email' => $event->model->email ?? 'N/A',
            'role_name' => $event->role->name,
            'assigned_by' => auth()->id(),
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString()
        ]);
    }
}
