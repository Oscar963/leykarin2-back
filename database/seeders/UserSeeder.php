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

        // Administrador del Sistema
        $adminSistema = User::updateOrCreate(
            ['email' => 'admin.sistema@demo.com'],
            [
                'rut' => '10000000-0',
                'name' => 'Admin',
                'paternal_surname' => 'Sistema',
                'maternal_surname' => 'Demo',
                'email' => 'admin.sistema@demo.com',
                'status' => 1,
                'password' => Hash::make('password123'),
                'type_user_id' => 1,
            ]
        );
        $adminSistema->assignRole('Administrador del Sistema');

        // Directores de las diferentes direcciones
        $directores = [
            // id = 1 (usado para Juzgados y otras direcciones)
            [
                'email' => 'director.juzgado@demo.com',
                'rut' => '11111111-1',
                'name' => 'Director',
                'paternal_surname' => 'Juzgado',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 2
            [
                'email' => 'director.alcaldia@demo.com',
                'rut' => '22222222-2',
                'name' => 'Director',
                'paternal_surname' => 'Alcaldia',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 3
            [
                'email' => 'director.gabinete@demo.com',
                'rut' => '33333333-3',
                'name' => 'Director',
                'paternal_surname' => 'Gabinete',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 4
            [
                'email' => 'director.secplan@demo.com',
                'rut' => '44444444-4',
                'name' => 'Director',
                'paternal_surname' => 'Secplan',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 5
            [
                'email' => 'director.secmunicipal@demo.com',
                'rut' => '55555555-5',
                'name' => 'Director',
                'paternal_surname' => 'SecMunicipal',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 6
            [
                'email' => 'director.daf@demo.com',
                'rut' => '66666666-6',
                'name' => 'Director',
                'paternal_surname' => 'DAF',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 7
            [
                'email' => 'director.dimao@demo.com',
                'rut' => '77777777-7',
                'name' => 'Director',
                'paternal_surname' => 'DIMAO',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 8
            [
                'email' => 'director.didec@demo.com',
                'rut' => '88888888-8',
                'name' => 'Director',
                'paternal_surname' => 'DIDEC',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 9
            [
                'email' => 'director.dom@demo.com',
                'rut' => '99999999-9',
                'name' => 'Director',
                'paternal_surname' => 'DOM',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 10
            [
                'email' => 'director.transito@demo.com',
                'rut' => '10101010-0',
                'name' => 'Director',
                'paternal_surname' => 'Transito',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 11
            [
                'email' => 'director.dipreseh@demo.com',
                'rut' => '11111112-1',
                'name' => 'Director',
                'paternal_surname' => 'DIPRESEH',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 12
            [
                'email' => 'director.rural@demo.com',
                'rut' => '12121212-2',
                'name' => 'Director',
                'paternal_surname' => 'Rural',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 13
            [
                'email' => 'director.cultura@demo.com',
                'rut' => '13131313-3',
                'name' => 'Director',
                'paternal_surname' => 'Cultura',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 14
            [
                'email' => 'director.turismo@demo.com',
                'rut' => '14141414-4',
                'name' => 'Director',
                'paternal_surname' => 'Turismo',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 15
            [
                'email' => 'director.disam@demo.com',
                'rut' => '15151515-5',
                'name' => 'Director',
                'paternal_surname' => 'DISAM',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
            // id = 16
            [
                'email' => 'director.demuce@demo.com',
                'rut' => '16161616-6',
                'name' => 'Director',
                'paternal_surname' => 'DEMUCE',
                'maternal_surname' => 'Demo',
                'type_user_id' => 2,
            ],
        ];

        foreach ($directores as $director) {
            $user = User::updateOrCreate(
                ['email' => $director['email']],
                [
                    'rut' => $director['rut'],
                    'name' => $director['name'],
                    'paternal_surname' => $director['paternal_surname'],
                    'maternal_surname' => $director['maternal_surname'],
                    'email' => $director['email'],
                    'status' => 1,
                    'password' => Hash::make('password123'),
                    'type_user_id' => $director['type_user_id'],
                ]
            );
            $user->assignRole('Director');
        }

        $this->command->info('Directores creados exitosamente');
    }
}
