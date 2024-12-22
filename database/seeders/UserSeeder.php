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
        User::updateOrCreate(
            ['email' => 'oscar.apata01@gmail.com'], // Identificador único
            [
                'rut' => '68243787-1',
                'name' => 'Admin User',
                'paternal_surname' => 'Apata',
                'maternal_surname' => 'Tito',
                'email' => 'oscar.apata01@gmail.com',
                'estado' => 0,
                'password' => Hash::make('password123'), // Contraseña predeterminada
            ]
        );

        $this->command->info('Usuario por defecto creado: oscar.apata01@gmail.com');
    }
}
