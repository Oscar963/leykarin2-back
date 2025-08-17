<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BasicSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function protected_routes_require_authentication()
    {
        // Sin autenticación, debe fallar
        $response = $this->getJson('/api/v1/auth/user');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/inmuebles');
        $response->assertStatus(401);
    }

    /** @test */
    public function user_with_2fa_cannot_access_protected_routes_after_partial_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'two_factor_enabled' => true,
            'status' => true
        ]);

        // Login inicial - debe requerir 2FA y hacer logout
        $response = $this->postJson('/api/v1/auth/login', [
            'rut' => $user->rut,
            'password' => 'password123'
        ]);

        // Debe responder con 2FA requerido
        $response->assertStatus(200);
        $responseData = $response->json();
        $this->assertArrayHasKey('two_factor_required', $responseData['error'] ?? []);

        // Intentar acceder a ruta protegida - debe fallar porque no completó 2FA
        $response = $this->getJson('/api/v1/auth/user');
        $response->assertStatus(401);
    }

    /** @test */
    public function user_without_2fa_can_login_and_access_protected_routes()
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

        $response->assertStatus(200);
        $responseData = $response->json();
        $this->assertArrayHasKey('user', $responseData);

        // Debe poder acceder a rutas protegidas
        $response = $this->getJson('/api/v1/auth/user');
        $response->assertStatus(200);
    }

    /** @test */
    public function suspended_user_cannot_login()
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
    public function invalid_credentials_are_rejected()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'status' => true
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'rut' => $user->rut,
            'password' => 'wrong-password'
        ]);

        // Debe fallar con credenciales incorrectas
        $response->assertStatus(401);
    }
}
