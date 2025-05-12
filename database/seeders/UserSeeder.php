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

        // Crear usuario de funcionario IMA
        $funcionarioIma = User::updateOrCreate(
            ['email' => 'oscar.apata00@municipalidadarica.cl'],
            [
                'rut' => '69172593-6',
                'name' => 'Funcionario IMA',
                'paternal_surname' => 'Funcionario',
                'maternal_surname' => 'IMA',
                'email' => 'oscar.apata00@municipalidadarica.cl',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );

        // Asignar el rol al usuario
        $funcionarioIma->assignRole('Funcionario IMA');

        // Crear usuario de funcionario DISAM
        $funcionarioDisam = User::updateOrCreate(
            ['email' => 'oscar.apata01@municipalidadarica.cl'],
            [
                'rut' => '44249101-1',
                'name' => 'Funcionario DISAM',
                'paternal_surname' => 'Funcionario',
                'maternal_surname' => 'DISAM',
                'email' => 'oscar.apata01@municipalidadarica.cl',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );

        // Asignar el rol al usuario
        $funcionarioDisam->assignRole('Funcionario DISAM');

        // Crear usuario de funcionario DEMUCE
        $funcionarioDemuce = User::updateOrCreate(
            ['email' => 'oscar.apata02@municipalidadarica.cl'],
            [
                'rut' => '69898069-9',
                'name' => 'Funcionario DEMUCE',
                'paternal_surname' => 'Funcionario',
                'maternal_surname' => 'DEMUCE',
                'email' => 'oscar.apata02@municipalidadarica.cl',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );

        // Asignar el rol al usuario 
        $funcionarioDemuce->assignRole('Funcionario DEMUCE');

        // Mostrar mensaje en la consola
        $this->command->info('Usuario por defecto creado: oscar.apata01@gmail.com');
    }
}
