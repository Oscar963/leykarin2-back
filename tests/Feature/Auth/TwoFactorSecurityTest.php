<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Carbon\Carbon;

class TwoFactorSecurityTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /** @test */
    public function user_cannot_access_protected_routes_without_completing_2fa()
    {
        // Crear usuario con 2FA habilitado
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_enabled' => true,
            'status' => true
        ]);

        // 1. Login inicial - debe requerir 2FA
        $response = $this->postJson('/api/v1/auth/login', [
            'rut' => $user->rut,
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'error' => [
                        'message',
                        'two_factor_required'
                    ]
                ]);

        // 2. Intentar acceder a ruta protegida SIN completar 2FA - debe fallar
        $response = $this->getJson('/api/v1/auth/user');
        $response->assertStatus(401);

        // 3. Intentar acceder a otras rutas protegidas - debe fallar
        $protectedRoutes = [
            '/api/v1/inmuebles',
            '/api/v1/users',
            '/api/v1/auth/two-factor-status'
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->getJson($route);
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function user_cannot_bypass_2fa_with_invalid_session_manipulation()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_enabled' => true,
            'status' => true
        ]);

        // Login inicial
        $this->postJson('/api/v1/auth/login', [
            'rut' => $user->rut,
            'password' => 'password123'
        ]);

        // Intentar manipular la sesión directamente
        session(['login.id' => $user->id, 'login.remember' => false]);

        // Aún así no debe poder acceder sin completar 2FA
        $response = $this->getJson('/api/v1/auth/user');
        $response->assertStatus(401);
    }

    /** @test */
    public function expired_2fa_codes_are_rejected()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_enabled' => true,
            'status' => true
        ]);

        // Login inicial
        $this->postJson('/api/v1/auth/login', [
            'rut' => $user->rut,
            'password' => 'password123'
        ]);

        // Simular código expirado
        $user->update([
            'two_factor_code' => '123456',
            'two_factor_expires_at' => Carbon::now()->subMinutes(15) // Expirado
        ]);

        // Intentar usar código expirado
        $response = $this->postJson('/api/v1/auth/two-factor-challenge', [
            'code' => '123456'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => [
                    'error_type' => 'invalid_two_factor_code'
                ]
            ]);
    }

    /** @test */
    public function invalid_2fa_codes_are_rejected()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_enabled' => true,
            'status' => true
        ]);

        // Login inicial
        $this->postJson('/api/v1/auth/login', [
            'rut' => $user->rut,
            'password' => 'password123'
        ]);

        // Intentar con código incorrecto
        $response = $this->postJson('/api/v1/auth/two-factor-challenge', [
            'code' => '999999'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => [
                    'error_type' => 'invalid_two_factor_code'
                ]
            ]);
    }

    /** @test */
    public function valid_2fa_code_grants_full_access()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_enabled' => true,
            'status' => true
        ]);

        // Login inicial
        $this->postJson('/api/v1/auth/login', [
            'rut' => $user->rut,
            'password' => 'password123'
        ]);

        // Obtener el código generado
        $user->refresh();
        $validCode = $user->two_factor_code;

        // Completar 2FA con código válido
        $response = $this->postJson('/api/v1/auth/two-factor-challenge', [
            'code' => $validCode
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user']);

        // Ahora debe poder acceder a rutas protegidas
        $response = $this->getJson('/api/v1/auth/user');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_without_2fa_can_login_directly()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_enabled' => false,
            'status' => true
        ]);

        // Login debe ser exitoso directamente
        $response = $this->postJson('/api/v1/auth/login', [
            'rut' => $user->rut,
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user'])
            ->assertJsonMissing(['two_factor_required']);

        // Debe poder acceder a rutas protegidas inmediatamente
        $response = $this->getJson('/api/v1/auth/user');
        $response->assertStatus(200);
    }

    /** @test */
    public function suspended_user_cannot_login_even_with_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'status' => false // Usuario suspendido
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'rut' => $user->rut,
            'password' => 'password123'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function authenticated_user_loses_access_when_suspended()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_enabled' => false,
            'status' => true
        ]);

        // Login exitoso
        $this->postJson('/api/v1/auth/login', [
            'rut' => $user->rut,
            'password' => 'password123'
        ]);

        // Verificar acceso inicial
        $response = $this->getJson('/api/v1/auth/user');
        $response->assertStatus(200);

        // Suspender usuario
        $user->update(['status' => false]);

        // Debe perder acceso inmediatamente
        $response = $this->getJson('/api/v1/auth/user');
        $response->assertStatus(403);
    }

    /** @test */
    public function two_factor_session_is_isolated_and_cannot_be_reused()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_enabled' => true,
            'status' => true
        ]);

        // Primer login
        $this->postJson('/api/v1/auth/login', [
            'rut' => $user->rut,
            'password' => 'password123'
        ]);

        $firstCode = $user->fresh()->two_factor_code;

        // Logout
        $this->postJson('/api/v1/auth/logout');

        // Segundo login (debe generar nuevo código)
        $this->postJson('/api/v1/auth/login', [
            'rut' => $user->rut,
            'password' => 'password123'
        ]);

        $secondCode = $user->fresh()->two_factor_code;

        // Los códigos deben ser diferentes
        $this->assertNotEquals($firstCode, $secondCode);

        // El código anterior no debe funcionar
        $response = $this->postJson('/api/v1/auth/two-factor-challenge', [
            'code' => $firstCode
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function two_factor_codes_are_properly_cleared_after_use()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_enabled' => true,
            'status' => true
        ]);

        // Login inicial
        $this->postJson('/api/v1/auth/login', [
            'rut' => $user->rut,
            'password' => 'password123'
        ]);

        $validCode = $user->fresh()->two_factor_code;

        // Completar 2FA
        $this->postJson('/api/v1/auth/two-factor-challenge', [
            'code' => $validCode
        ]);

        // El código debe estar limpio en la base de datos
        $user->refresh();
        $this->assertNull($user->two_factor_code);
        $this->assertNull($user->two_factor_expires_at);
        $this->assertNotNull($user->two_factor_confirmed_at);
    }
}
