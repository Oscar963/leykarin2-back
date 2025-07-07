<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\RateLimiter;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
        
        // Limpiar rate limiting antes de cada test
        RateLimiter::clear('login');
        RateLimiter::clear('forgot-password');
        RateLimiter::clear('reset-password');
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
    public function login_requires_password()
    {
        $response = $this->postJson('/api/login', [
            'rut' => '12345678-9'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function login_requires_rut()
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['rut']);
    }

    /** @test */
    public function rate_limiting_blocks_excessive_login_attempts()
    {
        // Intentar login 6 veces (límite es 5 por minuto)
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'rut' => '12345678-9',
                'password' => 'wrongpassword'
            ]);
        }

        // El sexto intento debería ser bloqueado
        $response = $this->postJson('/api/login', [
            'rut' => '12345678-9',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function rate_limiting_blocks_excessive_forgot_password_attempts()
    {
        // Intentar forgot password 6 veces (límite es 5 por minuto)
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/forgot-password', [
                'email' => 'test@example.com'
            ]);
        }

        // El sexto intento debería ser bloqueado
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function rate_limiting_blocks_excessive_reset_password_attempts()
    {
        // Intentar reset password 4 veces (límite es 3 por minuto)
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/reset-password', [
                'token' => 'test-token',
                'email' => 'test@example.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);
        }

        // El cuarto intento debería ser bloqueado
        $response = $this->postJson('/api/reset-password', [
            'token' => 'test-token',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(429); // Too Many Requests
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
    public function rate_limiting_blocks_excessive_logout_attempts()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Intentar logout 11 veces (límite es 10 por minuto)
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/logout');
        }

        // El undécimo intento debería ser bloqueado
        $response = $this->postJson('/api/logout');
        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function authenticated_user_can_get_profile()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'name', 'paternal_surname', 'maternal_surname', 'rut', 'email', 'status',
                        'roles', 'permissions'
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
    public function login_attempts_are_logged_for_security()
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->with('Login attempt', \Mockery::any())
            ->once();

        $user = User::factory()->create([
            'rut' => '12345678-9',
            'password' => Hash::make('password123'),
            'status' => true
        ]);

        $this->postJson('/api/login', [
            'rut' => '12345678-9',
            'password' => 'password123'
        ]);
    }

    /** @test */
    public function failed_login_attempts_are_logged_for_security()
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->andReturnSelf();
        
        Log::shouldReceive('warning')
            ->with('Failed login attempt', \Mockery::any())
            ->once();

        $this->postJson('/api/login', [
            'rut' => '12345678-9',
            'password' => 'wrongpassword'
        ]);
    }

    /** @test */
    public function login_with_sql_injection_attempt_is_blocked()
    {
        $response = $this->postJson('/api/login', [
            'rut' => "12345678-9'; DROP TABLE users; --",
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['rut']);
    }

    /** @test */
    public function login_with_xss_attempt_is_sanitized()
    {
        $response = $this->postJson('/api/login', [
            'rut' => '<script>alert("xss")</script>',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['rut']);
    }

    /** @test */
    public function password_reset_requires_valid_email_format()
    {
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function password_reset_requires_email()
    {
        $response = $this->postJson('/api/forgot-password', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function reset_password_requires_all_fields()
    {
        $response = $this->postJson('/api/reset-password', [
            'token' => 'test-token'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'password', 'password_confirmation']);
    }

    /** @test */
    public function reset_password_requires_matching_passwords()
    {
        $response = $this->postJson('/api/reset-password', [
            'token' => 'test-token',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function reset_password_requires_strong_password()
    {
        $response = $this->postJson('/api/reset-password', [
            'token' => 'test-token',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
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