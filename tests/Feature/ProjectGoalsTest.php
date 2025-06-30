<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Goal;
use App\Models\PurchasePlan;
use App\Models\Direction;
use App\Models\TypeProject;
use App\Models\UnitPurchasing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ProjectGoalsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $direction;
    protected $purchasePlan;
    protected $strategicType;
    protected $operativeType;
    protected $unitPurchasing;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed necesarios
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'TypeProjectSeeder']);
        $this->artisan('db:seed', ['--class' => 'UnitPurchasingSeeder']);

        // Crear datos de prueba
        $this->direction = Direction::factory()->create();
        $this->user = User::factory()->create();
        $this->user->assignRole('Director');
        $this->user->directions()->attach($this->direction);

        $this->purchasePlan = PurchasePlan::factory()->create([
            'direction_id' => $this->direction->id
        ]);

        $this->strategicType = TypeProject::where('name', 'Estratégico')->first();
        $this->operativeType = TypeProject::where('name', 'Operativo')->first();
        $this->unitPurchasing = UnitPurchasing::first();

        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function can_create_strategic_project_with_goals()
    {
        $projectData = [
            'name' => 'Proyecto Estratégico con Metas',
            'description' => 'Descripción del proyecto estratégico',
            'unit_purchasing_id' => $this->unitPurchasing->id,
            'type_project_id' => $this->strategicType->id,
            'purchase_plan_id' => $this->purchasePlan->id,
            'goals' => [
                [
                    'name' => 'Meta de prueba',
                    'description' => 'Descripción de la meta',
                    'target_value' => 100,
                    'progress_value' => 0,
                    'unit_measure' => 'unidades',
                    'target_date' => '2025-12-31',
                    'notes' => 'Notas de la meta'
                ]
            ]
        ];

        $response = $this->postJson('/api/projects', $projectData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id', 'name', 'is_strategic',
                        'goals' => [
                            '*' => ['id', 'name', 'target_value', 'progress_value']
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Proyecto Estratégico con Metas'
        ]);

        $this->assertDatabaseHas('goals', [
            'name' => 'Meta de prueba',
            'target_value' => 100
        ]);
    }

    /** @test */
    public function cannot_create_goals_for_operative_project()
    {
        $projectData = [
            'name' => 'Proyecto Operativo',
            'description' => 'Descripción del proyecto operativo',
            'unit_purchasing_id' => $this->unitPurchasing->id,
            'type_project_id' => $this->operativeType->id,
            'purchase_plan_id' => $this->purchasePlan->id,
            'goals' => [
                [
                    'name' => 'Meta inválida',
                    'description' => 'No debería crearse'
                ]
            ]
        ];

        $response = $this->postJson('/api/projects', $projectData);

        $response->assertStatus(500);
        
        $this->assertDatabaseMissing('goals', [
            'name' => 'Meta inválida'
        ]);
    }

    /** @test */
    public function can_update_project_goals()
    {
        $project = Project::factory()->create([
            'type_project_id' => $this->strategicType->id,
            'purchase_plan_id' => $this->purchasePlan->id
        ]);

        $goal = Goal::factory()->create([
            'project_id' => $project->id,
            'name' => 'Meta original'
        ]);

        $updateData = [
            'name' => $project->name,
            'description' => $project->description,
            'unit_purchasing_id' => $project->unit_purchasing_id,
            'type_project_id' => $project->type_project_id,
            'goals' => [
                [
                    'name' => 'Meta actualizada',
                    'description' => 'Nueva descripción',
                    'target_value' => 200,
                    'progress_value' => 50
                ]
            ]
        ];

        $response = $this->putJson("/api/projects/{$project->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('goals', [
            'project_id' => $project->id,
            'name' => 'Meta actualizada',
            'target_value' => 200
        ]);

        $this->assertDatabaseMissing('goals', [
            'name' => 'Meta original'
        ]);
    }

    /** @test */
    public function can_update_goal_progress()
    {
        $project = Project::factory()->create([
            'type_project_id' => $this->strategicType->id
        ]);

        $goal = Goal::factory()->create([
            'project_id' => $project->id,
            'target_value' => 100,
            'progress_value' => 0
        ]);

        $progressData = [
            'progress_value' => 75,
            'notes' => 'Progreso actualizado en tests'
        ];

        $response = $this->putJson("/api/goals/{$goal->id}/progress", $progressData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => ['progress_percentage'],
                    'progress_info' => [
                        'percentage', 'description', 'remaining_value', 
                        'is_completed', 'calculated_status'
                    ]
                ]);

        $this->assertDatabaseHas('goals', [
            'id' => $goal->id,
            'progress_value' => 75
        ]);
    }

    /** @test */
    public function goal_automatically_calculates_status()
    {
        $project = Project::factory()->create([
            'type_project_id' => $this->strategicType->id
        ]);

        $goal = Goal::factory()->create([
            'project_id' => $project->id,
            'target_value' => 100,
            'progress_value' => 0,
            'status' => 'pendiente'
        ]);

        // Actualizar progreso al 100%
        $response = $this->putJson("/api/goals/{$goal->id}/progress", [
            'progress_value' => 100
        ]);

        $response->assertStatus(200);

        $goal->refresh();
        $this->assertEquals('completada', $goal->status);
        $this->assertTrue($goal->isCompleted());
    }

    /** @test */
    public function can_get_project_goal_statistics()
    {
        $project = Project::factory()->create([
            'type_project_id' => $this->strategicType->id
        ]);

        // Crear múltiples metas
        Goal::factory()->create([
            'project_id' => $project->id,
            'target_value' => 100,
            'progress_value' => 100, // Completada
            'status' => 'completada'
        ]);

        Goal::factory()->create([
            'project_id' => $project->id,
            'target_value' => 100,
            'progress_value' => 50, // En progreso
            'status' => 'en_progreso'
        ]);

        $response = $this->getJson("/api/goals/project/{$project->id}/statistics");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_goals',
                        'completed_goals',
                        'in_progress_goals',
                        'average_progress',
                        'completion_percentage'
                    ]
                ]);

        $data = $response->json('data');
        $this->assertEquals(2, $data['total_goals']);
        $this->assertEquals(1, $data['completed_goals']);
        $this->assertEquals(50.0, $data['completion_percentage']);
    }

    /** @test */
    public function can_get_overdue_goals()
    {
        $project = Project::factory()->create([
            'type_project_id' => $this->strategicType->id
        ]);

        Goal::factory()->create([
            'project_id' => $project->id,
            'target_date' => now()->subDays(5), // Vencida
            'status' => 'en_progreso'
        ]);

        $response = $this->getJson('/api/goals/overdue');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'target_date', 'is_overdue']
                    ]
                ]);
    }

    /** @test */
    public function project_calculates_goal_statistics_correctly()
    {
        $project = Project::factory()->create([
            'type_project_id' => $this->strategicType->id
        ]);

        // Crear metas con diferentes progreso
        Goal::factory()->create([
            'project_id' => $project->id,
            'target_value' => 100,
            'progress_value' => 100
        ]);

        Goal::factory()->create([
            'project_id' => $project->id,
            'target_value' => 100,
            'progress_value' => 50
        ]);

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
                ->assertJsonPath('data.goal_statistics.total_goals', 2)
                ->assertJsonPath('data.goal_statistics.average_progress', 75.0);
    }

    /** @test */
    public function validates_goal_data_correctly()
    {
        $projectData = [
            'name' => 'Proyecto con meta inválida',
            'description' => 'Descripción',
            'unit_purchasing_id' => $this->unitPurchasing->id,
            'type_project_id' => $this->strategicType->id,
            'purchase_plan_id' => $this->purchasePlan->id,
            'goals' => [
                [
                    // name faltante - debería fallar
                    'description' => 'Meta sin nombre',
                    'target_value' => -10 // Valor negativo - debería fallar
                ]
            ]
        ];

        $response = $this->postJson('/api/projects', $projectData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['goals.0.name']);
    }
} 