<?php

namespace Tests\Feature;

use App\Models\Direction;
use App\Models\PurchasePlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class PurchasePlanUniqueValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $direction;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear los permisos necesarios para planes de compra
        Permission::firstOrCreate(['name' => 'purchase_plans.list', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'purchase_plans.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'purchase_plans.edit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'purchase_plans.delete', 'guard_name' => 'web']);

        // Crear un usuario con permisos
        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            'purchase_plans.list',
            'purchase_plans.create', 
            'purchase_plans.edit',
            'purchase_plans.delete'
        ]);
        
        // Crear una dirección
        $this->direction = Direction::factory()->create();
    }

    /** @test */
    public function it_prevents_creating_duplicate_plans_for_same_direction_and_year()
    {
        // Crear un plan de compra inicial directamente
        $existingPlan = PurchasePlan::create([
            'name' => 'Plan Original',
            'token' => Str::random(32),
            'year' => 2024,
            'direction_id' => $this->direction->id,
            'created_by' => $this->user->id
        ]);

        // Intentar crear otro plan para la misma dirección y año
        $response = $this->actingAs($this->user)
            ->postJson('/api/purchase-plans', [
                'name' => 'Plan Duplicado',
                'year' => 2024,
                'direction' => $this->direction->id
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['year'])
            ->assertJson([
                'errors' => [
                    'year' => [
                        "Ya existe un plan de compras para {$this->direction->name} en el año 2024. No se puede crear otro plan para la misma dirección y año."
                    ]
                ]
            ]);

        // Verificar que solo existe un plan
        $this->assertEquals(1, PurchasePlan::where('direction_id', $this->direction->id)
            ->where('year', 2024)
            ->count());
    }

    /** @test */
    public function it_prevents_updating_plan_to_duplicate_direction_and_year()
    {
        $direction2 = Direction::factory()->create();

        // Crear dos planes diferentes directamente
        $plan1 = PurchasePlan::create([
            'name' => 'Plan 1',
            'token' => Str::random(32),
            'year' => 2024,
            'direction_id' => $this->direction->id,
            'created_by' => $this->user->id
        ]);

        $plan2 = PurchasePlan::create([
            'name' => 'Plan 2',
            'token' => Str::random(32),
            'year' => 2025,
            'direction_id' => $direction2->id,
            'created_by' => $this->user->id
        ]);

        // Intentar actualizar el segundo plan para usar la misma dirección y año que el primero
        $response = $this->actingAs($this->user)
            ->putJson("/api/purchase-plans/{$plan2->id}", [
                'name' => 'Plan Actualizado',
                'year' => 2024,
                'direction' => $this->direction->id
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['year'])
            ->assertJson([
                'errors' => [
                    'year' => [
                        "Ya existe un plan de compras para {$this->direction->name} en el año 2024. No se puede crear otro plan para la misma dirección y año."
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_validates_unique_direction_year_rule_works_correctly()
    {
        // Verificar que no existe un plan para esta dirección y año
        $this->assertFalse(PurchasePlan::existsForDirectionAndYear($this->direction->id, 2024));

        // Crear un plan
        PurchasePlan::create([
            'name' => 'Plan Test',
            'token' => Str::random(32),
            'year' => 2024,
            'direction_id' => $this->direction->id,
            'created_by' => $this->user->id
        ]);

        // Verificar que ahora existe un plan para esta dirección y año
        $this->assertTrue(PurchasePlan::existsForDirectionAndYear($this->direction->id, 2024));

        // Verificar que no existe para otro año
        $this->assertFalse(PurchasePlan::existsForDirectionAndYear($this->direction->id, 2025));

        // Verificar que no existe para otra dirección
        $direction2 = Direction::factory()->create();
        $this->assertFalse(PurchasePlan::existsForDirectionAndYear($direction2->id, 2024));
    }

    /** @test */
    public function it_allows_creating_plans_for_different_years()
    {
        // Crear un plan para 2024 directamente
        PurchasePlan::create([
            'name' => 'Plan 2024',
            'token' => Str::random(32),
            'year' => 2024,
            'direction_id' => $this->direction->id,
            'created_by' => $this->user->id
        ]);

        // Verificar que se puede crear un plan para 2025
        $this->assertFalse(PurchasePlan::existsForDirectionAndYear($this->direction->id, 2025));

        // Crear un plan para 2025
        PurchasePlan::create([
            'name' => 'Plan 2025',
            'token' => Str::random(32),
            'year' => 2025,
            'direction_id' => $this->direction->id,
            'created_by' => $this->user->id
        ]);

        // Verificar que existen dos planes
        $this->assertEquals(2, PurchasePlan::where('direction_id', $this->direction->id)->count());
    }

    /** @test */
    public function it_allows_creating_plans_for_different_directions()
    {
        $direction2 = Direction::factory()->create();

        // Crear un plan para la primera dirección directamente
        PurchasePlan::create([
            'name' => 'Plan Dirección 1',
            'token' => Str::random(32),
            'year' => 2024,
            'direction_id' => $this->direction->id,
            'created_by' => $this->user->id
        ]);

        // Verificar que se puede crear un plan para la segunda dirección
        $this->assertFalse(PurchasePlan::existsForDirectionAndYear($direction2->id, 2024));

        // Crear un plan para la segunda dirección
        PurchasePlan::create([
            'name' => 'Plan Dirección 2',
            'token' => Str::random(32),
            'year' => 2024,
            'direction_id' => $direction2->id,
            'created_by' => $this->user->id
        ]);

        // Verificar que existen dos planes
        $this->assertEquals(2, PurchasePlan::count());
    }
} 