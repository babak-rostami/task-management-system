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

    // Mock external services
    $this->cacheMock = Mockery::mock(TaskCacheService::class);
    $this->loggerMock = Mockery::mock(LogInterface::class);

    app()->instance(TaskCacheService::class, $this->cacheMock);
    app()->instance(LogInterface::class, $this->loggerMock);
});

/**
 * page=1 and no filters → cache SHOULD be used
 */
it('uses cache when page is 1 and no filters', function () {

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
it('does not use cache when search filter is applied', function () {

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
it('does not use cache when status filter is applied', function () {

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
it('does not use cache when page is not 1', function () {

    $this->cacheMock->shouldReceive('rememberUserTasks')->never();

    Sanctum::actingAs($this->user);

    $response = getJson('/api/v1/tasks?page=2');

    $response->assertOk()
        ->assertJsonStructure([
            'data'
        ]);
});
