<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Crear o actualizar el usuario administrador
        $admin = User::updateOrCreate(
            ['email' => 'oscar.apata01@gmail.com'], // Identificador único
            [
                'rut' => '68243787-1',
                'name' => 'Admin User',
                'paternal_surname' => 'Apata',
                'maternal_surname' => 'Tito',
                'email' => 'oscar.apata01@gmail.com',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );

        // Asignar el rol al usuario
        $admin->assignRole('Administrador');

        // Mostrar mensaje en la consola
        $this->command->info('Usuario por defecto creado: oscar.apata01@gmail.com');
    }
}
