<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PublicationMonth;

class PublicationMonthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
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

        // Crear meses para los años 2024, 2025, 2026
        $years = [2024, 2025, 2026];
        
        foreach ($years as $year) {
            foreach ($months as $month) {
                PublicationMonth::firstOrCreate(
                    [
                        'month_number' => $month['month_number'],
                        'year' => $year
                    ],
                    [
                        'name' => $month['name'],
                        'short_name' => $month['short_name'],
                        'month_number' => $month['month_number'],
                        'year' => $year
                    ]
                );
            }
        }
    }
} 