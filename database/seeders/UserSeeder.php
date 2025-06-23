<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Direction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
                'rut' => '12345678-5',
                'name' => 'Admin',
                'paternal_surname' => 'Sistema',
                'maternal_surname' => 'Demo',
                'email' => 'admin.sistema@demo.com',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );
        $adminSistema->assignRole('Administrador del Sistema');
        
        // Administrador Municipal
        $adminMunicipal = User::updateOrCreate(
            ['email' => 'admin.municipal@demo.com'],
            [
                'rut' => '65623190-4',
                'name' => 'Admin',
                'paternal_surname' => 'Municipal',
                'maternal_surname' => 'Demo',
                'email' => 'admin.municipal@demo.com',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );
        $adminMunicipal->assignRole('Administrador Municipal');

        // Visador o de Administrador Municipal
        $visadorAdmin = User::updateOrCreate(
            ['email' => 'visador.admin@demo.com'],
            [
                'rut' => '29599766-4',
                'name' => 'Visador',
                'paternal_surname' => 'Admin',
                'maternal_surname' => 'Demo',
                'email' => 'visador.admin@demo.com',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );
        $visadorAdmin->assignRole('Visador o de Administrador Municipal');

        // Encargado de Presupuestos (antes Secretaría Comunal de Planificación)
        $encargadoPresupuestos = User::updateOrCreate(
            ['email' => 'encargado.presupuestos@demo.com'],
            [
                'rut' => '31016415-1',
                'name' => 'Encargado',
                'paternal_surname' => 'Presupuestos',
                'maternal_surname' => 'Demo',
                'email' => 'encargado.presupuestos@demo.com',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );
        $encargadoPresupuestos->assignRole('Encargado de Presupuestos');

        // Subrogante de Encargado de Presupuestos
        $subroganteEncargado = User::updateOrCreate(
            ['email' => 'subrogante.encargado@demo.com'],
            [
                'rut' => '86879809-2',
                'name' => 'Subrogante',
                'paternal_surname' => 'Encargado',
                'maternal_surname' => 'Demo',
                'email' => 'subrogante.encargado@demo.com',
                'status' => 1,
                'password' => Hash::make('password123'),
            ]
        );
        $subroganteEncargado->assignRole('Subrogante de Encargado de Presupuestos');

        // Directores de las diferentes direcciones
        $directores = [
            // id = 1 (usado para Juzgados y otras direcciones)
            [
                'email' => 'director.juzgado@demo.com',
                'rut' => '60433610-4',
                'name' => 'Director',
                'paternal_surname' => 'Juzgado',
                'maternal_surname' => 'Demo',
            ],
            // id = 2
            [
                'email' => 'director.alcaldia@demo.com',
                'rut' => '79710858-8',
                'name' => 'Director',
                'paternal_surname' => 'Alcaldia',
                'maternal_surname' => 'Demo',
            ],
            // id = 3
            [
                'email' => 'director.gabinete@demo.com',
                'rut' => '52434731-8',
                'name' => 'Director',
                'paternal_surname' => 'Gabinete',
                'maternal_surname' => 'Demo',
            ],
            // id = 4
            [
                'email' => 'director.secplan@demo.com',
                'rut' => '90123456-9',
                'name' => 'Director',
                'paternal_surname' => 'Secplan',
                'maternal_surname' => 'Demo',
            ],
            // id = 5
            [
                'email' => 'director.secmunicipal@demo.com',
                'rut' => '01234567-7',
                'name' => 'Director',
                'paternal_surname' => 'SecMunicipal',
                'maternal_surname' => 'Demo',
            ],
            // id = 6
            [
                'email' => 'director.daf@demo.com',
                'rut' => '11223344-5',
                'name' => 'Director',
                'paternal_surname' => 'DAF',
                'maternal_surname' => 'Demo',
            ],
            // id = 7
            [
                'email' => 'director.dimao@demo.com',
                'rut' => '22334455-3',
                'name' => 'Director',
                'paternal_surname' => 'DIMAO',
                'maternal_surname' => 'Demo',
            ],
            // id = 8
            [
                'email' => 'director.didec@demo.com',
                'rut' => '33445566-1',
                'name' => 'Director',
                'paternal_surname' => 'DIDEC',
                'maternal_surname' => 'Demo',
            ],
            // id = 9
            [
                'email' => 'director.dom@demo.com',
                'rut' => '44556677-9',
                'name' => 'Director',
                'paternal_surname' => 'DOM',
                'maternal_surname' => 'Demo',
            ],
            // id = 10
            [
                'email' => 'director.transito@demo.com',
                'rut' => '55667788-7',
                'name' => 'Director',
                'paternal_surname' => 'Transito',
                'maternal_surname' => 'Demo',
            ],
            // id = 11
            [
                'email' => 'director.dipreseh@demo.com',
                'rut' => '66778899-5',
                'name' => 'Director',
                'paternal_surname' => 'DIPRESEH',
                'maternal_surname' => 'Demo',
            ],
            // id = 12
            [
                'email' => 'director.rural@demo.com',
                'rut' => '41731368-0',
                'name' => 'Director',
                'paternal_surname' => 'Rural',
                'maternal_surname' => 'Demo',
            ],
            // id = 13
            [
                'email' => 'director.cultura@demo.com',
                'rut' => '88990011-1',
                'name' => 'Director',
                'paternal_surname' => 'Cultura',
                'maternal_surname' => 'Demo',
            ],
            // id = 14
            [
                'email' => 'director.turismo@demo.com',
                'rut' => '99001122-9',
                'name' => 'Director',
                'paternal_surname' => 'Turismo',
                'maternal_surname' => 'Demo',
            ],
            // id = 15
            [
                'email' => 'director.disam@demo.com',
                'rut' => '00112233-7',
                'name' => 'Director',
                'paternal_surname' => 'DISAM',
                'maternal_surname' => 'Demo',
            ],
            // id = 16
            [
                'email' => 'director.demuce@demo.com',
                'rut' => '10203040-5',
                'name' => 'Director',
                'paternal_surname' => 'DEMUCE',
                'maternal_surname' => 'Demo',
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
                ]
            );
            $user->assignRole('Director');
        }

        // Crear algunos usuarios de ejemplo para otros roles
        $usuariosEjemplo = [
            [
                'email' => 'subrogante.director@demo.com',
                'rut' => '20304050-3',
                'name' => 'Subrogante',
                'paternal_surname' => 'Director',
                'maternal_surname' => 'Demo',
                'role' => 'Subrogante de Director'
            ],
            [
                'email' => 'jefatura@demo.com',
                'rut' => '30405060-1',
                'name' => 'Jefatura',
                'paternal_surname' => 'Demo',
                'maternal_surname' => 'Demo',
                'role' => 'Jefatura'
            ],
            [
                'email' => 'subrogante.jefatura@demo.com',
                'rut' => '40506070-9',
                'name' => 'Subrogante',
                'paternal_surname' => 'Jefatura',
                'maternal_surname' => 'Demo',
                'role' => 'Subrogante de Jefatura'
            ],
        ];

        foreach ($usuariosEjemplo as $usuario) {
            $user = User::updateOrCreate(
                ['email' => $usuario['email']],
                [
                    'rut' => $usuario['rut'],
                    'name' => $usuario['name'],
                    'paternal_surname' => $usuario['paternal_surname'],
                    'maternal_surname' => $usuario['maternal_surname'],
                    'email' => $usuario['email'],
                    'status' => 1,
                    'password' => Hash::make('password123'),
                ]
            );
            $user->assignRole($usuario['role']);
        }

        // Crear usuarios de ejemplo para diferentes direcciones
        $this->createExampleUsers();

        $this->command->info('Usuarios creados exitosamente');
    }

    /**
     * Crea usuarios de ejemplo para diferentes direcciones
     */
    private function createExampleUsers(): void
    {
        $exampleUsers = [
            // Usuarios de DAF
            [
                'email' => 'usuario.daf1@demo.com',
                'rut' => '50607080-7',
                'name' => 'Usuario',
                'paternal_surname' => 'DAF1',
                'maternal_surname' => 'Demo',
                'role' => 'Jefatura',
                'direction' => 'Dirección de Administración y Finanzas'
            ],
            [
                'email' => 'usuario.daf2@demo.com',
                'rut' => '60708090-5',
                'name' => 'Usuario',
                'paternal_surname' => 'DAF2',
                'maternal_surname' => 'Demo',
                'role' => 'Subrogante de Jefatura',
                'direction' => 'Dirección de Administración y Finanzas'
            ],
            
            // Usuarios de DIMAO
            [
                'email' => 'usuario.dimao1@demo.com',
                'rut' => '70809010-3',
                'name' => 'Usuario',
                'paternal_surname' => 'DIMAO1',
                'maternal_surname' => 'Demo',
                'role' => 'Jefatura',
                'direction' => 'Dirección de Medio Ambiente, Aseo y Ornato'
            ],
            
            // Usuarios de DOM
            [
                'email' => 'usuario.dom1@demo.com',
                'rut' => '80901020-1',
                'name' => 'Usuario',
                'paternal_surname' => 'DOM1',
                'maternal_surname' => 'Demo',
                'role' => 'Jefatura',
                'direction' => 'Dirección de Obras Municipales'
            ],
            [
                'email' => 'usuario.dom2@demo.com',
                'rut' => '91020304-9',
                'name' => 'Usuario',
                'paternal_surname' => 'DOM2',
                'maternal_surname' => 'Demo',
                'role' => 'Subrogante de Jefatura',
                'direction' => 'Dirección de Obras Municipales'
            ],
            
            // Usuarios de DIDEC
            [
                'email' => 'usuario.didec1@demo.com',
                'rut' => '11223344-6',
                'name' => 'Usuario',
                'paternal_surname' => 'DIDEC1',
                'maternal_surname' => 'Demo',
                'role' => 'Jefatura',
                'direction' => 'Dirección Desarrollo Comunitario'
            ],
            
            // Usuarios de DISAM
            [
                'email' => 'usuario.disam1@demo.com',
                'rut' => '22334455-4',
                'name' => 'Usuario',
                'paternal_surname' => 'DISAM1',
                'maternal_surname' => 'Demo',
                'role' => 'Jefatura',
                'direction' => 'Dirección de Salud Municipal'
            ],
            [
                'email' => 'usuario.disam2@demo.com',
                'rut' => '33445566-2',
                'name' => 'Usuario',
                'paternal_surname' => 'DISAM2',
                'maternal_surname' => 'Demo',
                'role' => 'Subrogante de Jefatura',
                'direction' => 'Dirección de Salud Municipal'
            ],
        ];

        foreach ($exampleUsers as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'rut' => $userData['rut'],
                    'name' => $userData['name'],
                    'paternal_surname' => $userData['paternal_surname'],
                    'maternal_surname' => $userData['maternal_surname'],
                    'email' => $userData['email'],
                    'status' => 1,
                    'password' => Hash::make('password123'),
                ]
            );
            
            $user->assignRole($userData['role']);
            
            // Asignar dirección
            $direction = Direction::where('name', $userData['direction'])->first();
            if ($direction) {
                DB::table('direction_user')->updateOrInsert(
                    [
                        'direction_id' => $direction->id,
                        'user_id' => $user->id
                    ],
                    [
                        'direction_id' => $direction->id,
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }

        $this->command->info('Usuarios de ejemplo creados exitosamente');
    }
}
