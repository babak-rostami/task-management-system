<?php

use App\Models\User;
use App\Services\Task\TaskCacheService;
use App\Services\Logging\LogInterface;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Sanctum\Sanctum;
use Mockery;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;


beforeEach(function () {

    // Seed roles & permissions
    seed(RolePermissionSeeder::class);

    // Create a user with required permission
    $this->user = User::factory()->create();
    $this->user->givePermissionTo('my.tasks');

    // Mock cache services
    $this->cacheMock = Mockery::mock(TaskCacheService::class);
    app()->instance(TaskCacheService::class, $this->cacheMock);
});

/**
 * page=1 and no filters → cache SHOULD be used
 */
test('uses cache when page is 1 and no filters', function () {

    Sanctum::actingAs($this->user);

    $this->cacheMock
        ->shouldReceive('rememberUserTasks')
        ->once()
        ->with($this->user)
        ->andReturn(collect([])); // returning empty collection for assertion

    $response = getJson('/api/v1/tasks');

    // Validate response structure (collection)
    $response->assertOk()
        ->assertJsonStructure([
            'data'
        ]);
});

/**
 * search filter applied → cache should NOT be used
 */
test('does not use cache when search filter is applied', function () {

    $this->cacheMock->shouldReceive('rememberUserTasks')->never();

    Sanctum::actingAs($this->user);

    $response = getJson('/api/v1/tasks?search=hello');

    $response->assertOk()
        ->assertJsonStructure([
            'data'
        ]);
});

/**
 * status filter applied → cache should NOT be used
 */
test('does not use cache when status filter is applied', function () {

    $this->cacheMock->shouldReceive('rememberUserTasks')->never();

    Sanctum::actingAs($this->user);

    $response = getJson('/api/v1/tasks?status=pending');

    $response->assertOk()
        ->assertJsonStructure([
            'data'
        ]);
});

/**
 * page != 1 → cache should NOT be used
 */
test('does not use cache when page is not 1', function () {

    $this->cacheMock->shouldReceive('rememberUserTasks')->never();

    Sanctum::actingAs($this->user);

    $response = getJson('/api/v1/tasks?page=2');

    $response->assertOk()
        ->assertJsonStructure([
            'data'
        ]);
});

/**
 * user without permission
 */
test('user can not access task index without my.tasks permission', function () {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = getJson('/api/v1/tasks');

    $response->assertStatus(403);
});

/**
 * cannot access without login
 */
test('user who is not logged in cannot access task index', function () {

    $response = getJson('/api/v1/tasks');

    $response->assertStatus(401);
});
