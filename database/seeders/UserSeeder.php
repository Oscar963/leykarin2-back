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

        //Gesto de denuncias IMA
        $gestorIma = User::updateOrCreate(
            ['email' => 'ima.contenido@demo.com'],
            [
                'rut' => RutHelper::normalize('59831887-5'),
                'name' => 'Gestor',
                'paternal_surname' => 'IMA',
                'maternal_surname' => 'Demo',
                'email' => 'ima.contenido@demo.com',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );
        $gestorIma->assignRole('Gestor de Denuncias IMA');

        // Gestor de denuncias DISAM
        $gestorDisam = User::updateOrCreate(
            ['email' => 'disam.contenido@demo.com'],
            [
                'rut' => RutHelper::normalize('44173636-3'),
                'name' => 'Gestor',
                'paternal_surname' => 'DISAM',
                'maternal_surname' => 'Demo',
                'email' => 'disam.contenido@demo.com',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );
        $gestorDisam->assignRole('Gestor de Denuncias DISAM');

        //Gesto de denuncias DEMUCE
        $gestorDemuce = User::updateOrCreate(
            ['email' => 'demuce.contenido@demo.com'],
            [
                'rut' => RutHelper::normalize('49750609-3'),
                'name' => 'Gestor',
                'paternal_surname' => 'DEMUCE',
                'maternal_surname' => 'Demo',
                'email' => 'demuce.contenido@demo.com',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );
        $gestorDemuce->assignRole('Gestor de Denuncias DEMUCE');

        $this->command->info('Usuarios creados correctamente con los nuevos roles.');
    }
}
