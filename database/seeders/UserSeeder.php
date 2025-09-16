<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Helpers\RutHelper;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Administrador del Sistema
        $adminSistema = User::updateOrCreate(
            ['email' => 'admin.sistema@demo.com'],
            [
                'rut' => RutHelper::normalize('12345678-5'),
                'name' => 'Admin',
                'paternal_surname' => 'Sistema',
                'maternal_surname' => 'Demo',
                'email' => 'oscar.apata01@gmail.com',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );
        $adminSistema->assignRole('Administrador del Sistema');

        // Gestor de Contenido
        $gestorContenido = User::updateOrCreate(
            ['email' => 'gestor.contenido@demo.com'],
            [
                'rut' => RutHelper::normalize('17323866-5'),
                'name' => 'Gestor',
                'paternal_surname' => 'Contenido',
                'maternal_surname' => 'Demo',
                'email' => 'gestor.contenido@demo.com',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );
        $gestorContenido->assignRole('Gestor de Denuncias');

        // Gestor de Contenido adicional
        $gestorContenido2 = User::updateOrCreate(
            ['email' => 'gestor.contenido2@demo.com'],
            [
                'rut' => RutHelper::normalize('30294724-4'),
                'name' => 'Gestor',
                'paternal_surname' => 'Contenido',
                'maternal_surname' => 'Demo 2',
                'email' => 'gestor.contenido2@demo.com',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );
        $gestorContenido2->assignRole('Gestor de Denuncias');

        $this->command->info('Usuarios creados correctamente con los nuevos roles.');
    }
}
