<?php

namespace App\Services;

use App\Models\PublicationMonth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

class PublicationMonthService
{
    /**
     * Obtiene todos los meses de publicación ordenados por año descendente y mes ascendente
     *
     * @return Collection
     */
    public function getAllPublicationMonths(): Collection
    {
        return PublicationMonth::active()->get();
    }

    /**
     * Obtiene meses de publicación paginados
     *
     * @param int $perPage Número de elementos por página
     * @param string|null $query Término de búsqueda
     * @return LengthAwarePaginator
     */
    public function getPublicationMonthsPaginated(int $perPage = 15, ?string $query = null): LengthAwarePaginator
    {
        $queryBuilder = PublicationMonth::query();

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('short_name', 'LIKE', "%{$query}%")
                  ->orWhere('year', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->orderBy('year', 'desc')
                           ->orderBy('month_number')
                           ->paginate($perPage);
    }

    /**
     * Obtiene un mes de publicación por su ID
     *
     * @param int $id ID del mes de publicación
     * @return PublicationMonth
     * @throws Exception
     */
    public function getPublicationMonthById(int $id): PublicationMonth
    {
        $publicationMonth = PublicationMonth::find($id);

        if (!$publicationMonth) {
            throw new Exception('Mes de publicación no encontrado.');
        }

        return $publicationMonth;
    }

    /**
     * Obtiene meses de publicación por año específico
     *
     * @param int $year Año para filtrar
     * @return Collection
     */
    public function getPublicationMonthsByYear(int $year): Collection
    {
        return PublicationMonth::byYear($year)
                              ->orderBy('month_number')
                              ->get();
    }

    /**
     * Obtiene los años disponibles ordenados descendente
     *
     * @return Collection
     */
    public function getAvailableYears(): Collection
    {
        return PublicationMonth::select('year')
                              ->distinct()
                              ->orderBy('year', 'desc')
                              ->get();
    }

    /**
     * Obtiene meses con formato para select/dropdown
     *
     * @param int|null $year Año específico (opcional)
     * @return Collection
     */
    public function getPublicationMonthsForSelect(?int $year = null): Collection
    {
        $query = PublicationMonth::query();

        if ($year) {
            $query->byYear($year);
        }

        return $query->orderBy('year', 'desc')
                    ->orderBy('month_number')
                    ->get()
                    ->map(function ($month) {
                        return [
                            'id' => $month->id,
                            'name' => $month->name,
                            'short_name' => $month->short_name,
                            'month_number' => $month->month_number,
                            'year' => $month->year,
                            'display_name' => $month->formatted_date,
                            'full_display_name' => $month->name . ' ' . $month->year
                        ];
                    });
    }

    /**
     * Crea un nuevo mes de publicación
     *
     * @param array $data Datos del mes de publicación
     * @return PublicationMonth
     * @throws Exception
     */
    public function createPublicationMonth(array $data): PublicationMonth
    {
        // Validar que no exista ya el mismo mes y año
        $existingMonth = PublicationMonth::where('month_number', $data['month_number'])
                                        ->where('year', $data['year'])
                                        ->first();

        if ($existingMonth) {
            throw new Exception("Ya existe un registro para {$data['name']} del año {$data['year']}.");
        }

        return PublicationMonth::create($data);
    }

    /**
     * Actualiza un mes de publicación existente
     *
     * @param int $id ID del mes de publicación
     * @param array $data Datos actualizados
     * @return PublicationMonth
     * @throws Exception
     */
    public function updatePublicationMonth(int $id, array $data): PublicationMonth
    {
        $publicationMonth = $this->getPublicationMonthById($id);

        // Validar que no exista ya el mismo mes y año (excluyendo el actual)
        $existingMonth = PublicationMonth::where('month_number', $data['month_number'])
                                        ->where('year', $data['year'])
                                        ->where('id', '!=', $id)
                                        ->first();

        if ($existingMonth) {
            throw new Exception("Ya existe un registro para {$data['name']} del año {$data['year']}.");
        }

        $publicationMonth->update($data);
        return $publicationMonth->fresh();
    }

    /**
     * Elimina un mes de publicación
     *
     * @param int $id ID del mes de publicación
     * @return bool
     * @throws Exception
     */
    public function deletePublicationMonth(int $id): bool
    {
        $publicationMonth = $this->getPublicationMonthById($id);

        // Verificar si hay ítems de compra usando este mes
        if ($publicationMonth->itemPurchases()->count() > 0) {
            throw new Exception('No se puede eliminar el mes de publicación porque está siendo utilizado por ítems de compra.');
        }

        return $publicationMonth->delete();
    }

    /**
     * Crea meses para un año específico
     *
     * @param int $year Año para crear los meses
     * @return Collection
     */
    public function createMonthsForYear(int $year): Collection
    {
        $months = [
            ['name' => 'Enero', 'short_name' => 'Ene', 'month_number' => 1],
            ['name' => 'Febrero', 'short_name' => 'Feb', 'month_number' => 2],
            ['name' => 'Marzo', 'short_name' => 'Mar', 'month_number' => 3],
            ['name' => 'Abril', 'short_name' => 'Abr', 'month_number' => 4],
            ['name' => 'Mayo', 'short_name' => 'May', 'month_number' => 5],
            ['name' => 'Junio', 'short_name' => 'Jun', 'month_number' => 6],
            ['name' => 'Julio', 'short_name' => 'Jul', 'month_number' => 7],
            ['name' => 'Agosto', 'short_name' => 'Ago', 'month_number' => 8],
            ['name' => 'Septiembre', 'short_name' => 'Sep', 'month_number' => 9],
            ['name' => 'Octubre', 'short_name' => 'Oct', 'month_number' => 10],
            ['name' => 'Noviembre', 'short_name' => 'Nov', 'month_number' => 11],
            ['name' => 'Diciembre', 'short_name' => 'Dic', 'month_number' => 12],
        ];

        $createdMonths = collect();

        foreach ($months as $month) {
            try {
                $createdMonth = $this->createPublicationMonth(array_merge($month, ['year' => $year]));
                $createdMonths->push($createdMonth);
            } catch (Exception $e) {
                // Si ya existe, no hacer nada
                continue;
            }
        }

        return $createdMonths;
    }

    /**
     * Obtiene estadísticas de uso de meses de publicación
     *
     * @return array
     */
    public function getPublicationMonthsStats(): array
    {
        $totalMonths = PublicationMonth::count();
        $totalYears = PublicationMonth::select('year')->distinct()->count();
        $monthsWithItems = PublicationMonth::has('itemPurchases')->count();
        $unusedMonths = $totalMonths - $monthsWithItems;

        return [
            'total_months' => $totalMonths,
            'total_years' => $totalYears,
            'months_with_items' => $monthsWithItems,
            'unused_months' => $unusedMonths,
            'usage_percentage' => $totalMonths > 0 ? round(($monthsWithItems / $totalMonths) * 100, 2) : 0
        ];
    }

    /**
     * Busca meses de publicación por término
     *
     * @param string $term Término de búsqueda
     * @param int $limit Límite de resultados
     * @return Collection
     */
    public function searchPublicationMonths(string $term, int $limit = 10): Collection
    {
        return PublicationMonth::where('name', 'LIKE', "%{$term}%")
                              ->orWhere('short_name', 'LIKE', "%{$term}%")
                              ->orWhere('year', 'LIKE', "%{$term}%")
                              ->orderBy('year', 'desc')
                              ->orderBy('month_number')
                              ->limit($limit)
                              ->get();
    }
}
