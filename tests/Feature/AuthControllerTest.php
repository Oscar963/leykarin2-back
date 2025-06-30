<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Direction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'rut' => '12345678-9',
            'password' => Hash::make('password123'),
            'status' => true
        ]);

        $response = $this->postJson('/api/login', [
            'rut' => '12345678-9',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'user' => ['name', 'email']
                ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'rut' => '12345678-9',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'rut' => '12345678-9',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'status' => 401,
                    'error' => [
                        'message' => 'Credenciales incorrectas. Verifique su rut y contraseña e intente nuevamente.'
                    ]
                ]);
    }

    /** @test */
    public function suspended_user_cannot_login()
    {
        $user = User::factory()->create([
            'rut' => '12345678-9',
            'password' => Hash::make('password123'),
            'status' => false
        ]);

        $response = $this->postJson('/api/login', [
            'rut' => '12345678-9',
            'password' => 'password123'
        ]);

        $response->assertStatus(403)
                ->assertJson([
                    'status' => 403,
                    'error' => [
                        'message' => 'Tu cuenta está suspendida. Por favor contáctate con el administrador del sistema.'
                    ]
                ]);
    }

    /** @test */
    public function login_requires_valid_rut_format()
    {
        $response = $this->postJson('/api/login', [
            'rut' => 'invalid-rut',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['rut']);
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Cerró sesión exitosamente'
                ]);
    }

    /** @test */
    public function authenticated_user_can_get_profile()
    {
        $direction = Direction::factory()->create();
        $user = User::factory()->create();
        $user->directions()->attach($direction);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'name', 'rut', 'email', 'status',
                        'direction', 'direction_id', 'roles', 'permissions'
                    ]
                ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_check_authentication_status()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/isAuthenticated');

        $response->assertStatus(200)
                ->assertJson(['isAuthenticated' => true]);
    }

    /** @test */
    public function guest_receives_false_for_authentication_check()
    {
        $response = $this->getJson('/api/isAuthenticated');

        $response->assertStatus(200)
                ->assertJson(['isAuthenticated' => false]);
    }

    /** @test */
    public function user_can_get_permissions()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/permissions');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => ['name', 'email', 'rut'],
                    'roles',
                    'permissions'
                ]);
    }
} 