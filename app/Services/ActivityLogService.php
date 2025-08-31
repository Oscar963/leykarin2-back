<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ActivityLogService
{
    /**
     * Obtiene todos los logs de actividad ordenados por fecha de creación (descendente).
     * @return Collection<ActivityLog>
     */
    public function getAllActivityLogs(): Collection
    {
        return ActivityLog::latest()->get();
    }

    /**
     * Obtiene todos los logs de actividad con filtros y paginación.
     * @param ?string $query
     * @param ?int $perPage
     * @return LengthAwarePaginator<ActivityLog> 
     */
    public function getAllActivityLogsByQuery(?string $query, ?int $perPage = 15): LengthAwarePaginator
    {
        return ActivityLog::with('user') // Eager load para evitar N+1
            ->latest('id')
            ->when($query, function (Builder $q) use ($query) {
                $q->where(function ($subQ) use ($query) {
                    $subQ->where('user_id', 'LIKE', "%{$query}%")
                        ->orWhere('action', 'LIKE', "%{$query}%")
                        ->orWhere('details', 'LIKE', "%{$query}%")
                        ->orWhere('ip_address', 'LIKE', "%{$query}%")
                        ->orWhere('user_agent', 'LIKE', "%{$query}%")
                        ->orWhere('geolocation', 'LIKE', "%{$query}%")
                        ->orWhere('browser', 'LIKE', "%{$query}%")
                        ->orWhere('os', 'LIKE', "%{$query}%")
                        ->orWhere('referer', 'LIKE', "%{$query}%");
                })
                    // JOIN con users para buscar por nombre y apellido
                    ->orWhereHas('user', function ($userQ) use ($query) {
                        $userQ->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('paternal_surname', 'LIKE', "%{$query}%")
                            ->orWhere('maternal_surname', 'LIKE', "%{$query}%");
                    });
            })
            ->paginate($perPage);
    }
}
