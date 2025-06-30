<?php

namespace Tests\Unit;

use App\Http\Middleware\ValidateHierarchicalUserDirection;
use App\Http\Middleware\ValidateStrategicProject;
use App\Http\Middleware\CheckDirectionPermission;
use App\Models\User;
use App\Models\Direction;
use App\Models\Project;
use App\Models\TypeProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;
use Mockery;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'TypeProjectSeeder']);
    }

    /** @test */
    public function validate_hierarchical_user_direction_allows_admin_multiple_directions()
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador del Sistema');
        
        $direction1 = Direction::factory()->create();
        $direction2 = Direction::factory()->create();
        
        $request = Request::create('/api/directions/1/assign-users', 'POST', [
            'user_ids' => [$user->id],
            'direction_ids' => [$direction1->id, $direction2->id]
        ]);
        
        $middleware = new ValidateHierarchicalUserDirection();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });
        
        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function validate_hierarchical_user_direction_blocks_director_multiple_directions()
    {
        $user = User::factory()->create();
        $user->assignRole('Director');
        
        $direction1 = Direction::factory()->create();
        $direction2 = Direction::factory()->create();
        
        $request = Request::create('/api/directions/1/assign-users', 'POST', [
            'user_ids' => [$user->id],
            'direction_ids' => [$direction1->id, $direction2->id]
        ]);
        
        $middleware = new ValidateHierarchicalUserDirection();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });
        
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('solo puede pertenecer a una dirección', $response->getContent());
    }

    /** @test */
    public function validate_strategic_project_allows_goals_for_strategic_projects()
    {
        $strategicType = TypeProject::where('name', 'Estratégico')->first();
        $project = Project::factory()->create([
            'type_project_id' => $strategicType->id
        ]);
        
        $request = Request::create('/api/goals', 'POST', [
            'project_id' => $project->id,
            'name' => 'Test Goal'
        ]);
        
        $middleware = new ValidateStrategicProject();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });
        
        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function validate_strategic_project_blocks_goals_for_operative_projects()
    {
        $operativeType = TypeProject::where('name', 'Operativo')->first();
        $project = Project::factory()->create([
            'type_project_id' => $operativeType->id
        ]);
        
        $request = Request::create('/api/goals', 'POST', [
            'project_id' => $project->id,
            'name' => 'Test Goal'
        ]);
        
        $middleware = new ValidateStrategicProject();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });
        
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('solo se pueden crear metas en proyectos de tipo estratégico', $response->getContent());
    }

    /** @test */
    public function check_direction_permission_allows_admin_access_all_directions()
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador del Sistema');
        $this->actingAs($user);
        
        $direction = Direction::factory()->create();
        
        $request = Request::create('/api/purchase-plans', 'GET', [
            'direction_id' => $direction->id
        ]);
        
        $middleware = new CheckDirectionPermission();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        }, 'purchase_plans.list');
        
        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function check_direction_permission_blocks_unauthorized_direction_access()
    {
        $user = User::factory()->create();
        $user->assignRole('Director');
        $user->givePermissionTo('purchase_plans.list');
        
        $userDirection = Direction::factory()->create(['name' => 'User Direction']);
        $otherDirection = Direction::factory()->create(['name' => 'Other Direction']);
        
        $user->directions()->attach($userDirection);
        $this->actingAs($user);
        
        $request = Request::create('/api/purchase-plans', 'GET', [
            'direction_id' => $otherDirection->id
        ]);
        
        $middleware = new CheckDirectionPermission();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        }, 'purchase_plans.list');
        
        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function middleware_handles_nonexistent_project_gracefully()
    {
        $request = Request::create('/api/goals', 'POST', [
            'project_id' => 999999, // Non-existent project
            'name' => 'Test Goal'
        ]);
        
        $middleware = new ValidateStrategicProject();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });
        
        $this->assertEquals(404, $response->getStatusCode());
    }

    /** @test */
    public function middleware_skips_validation_for_non_goal_routes()
    {
        $request = Request::create('/api/projects', 'GET');
        
        $middleware = new ValidateStrategicProject();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });
        
        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function hierarchical_validation_works_for_user_creation()
    {
        $request = Request::create('/api/users', 'POST', [
            'roles' => ['Director'],
            'directions' => [1, 2] // Multiple directions for hierarchical user
        ]);
        
        $middleware = new ValidateHierarchicalUserDirection();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });
        
        $this->assertEquals(422, $response->getStatusCode());
    }

    /** @test */
    public function hierarchical_validation_allows_single_direction_for_directors()
    {
        $direction = Direction::factory()->create();
        
        $request = Request::create('/api/users', 'POST', [
            'roles' => ['Director'],
            'directions' => [$direction->id] // Single direction
        ]);
        
        $middleware = new ValidateHierarchicalUserDirection();
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });
        
        $this->assertEquals('OK', $response->getContent());
    }
} 