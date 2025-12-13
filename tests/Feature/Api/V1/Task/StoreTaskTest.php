<?php

use App\Models\User;
use App\Models\Task;
use App\Services\Task\TaskCacheService;
use App\Services\Logging\LogInterface;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;

use function Pest\Laravel\postJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {

    // Seed roles & permissions
    seed(RolePermissionSeeder::class);

    // Create user with create permission
    $this->user = User::factory()->create();
    $this->user->givePermissionTo('create.task');

    // Mock services
    $this->cacheMock = Mockery::mock(TaskCacheService::class);
    $this->loggerMock = Mockery::mock(LogInterface::class);

    app()->instance(TaskCacheService::class, $this->cacheMock);
    app()->instance(LogInterface::class, $this->loggerMock);
});

function validTaskPayload(): array
{
    return [
        'title' => 'Test Task',
        'description' => 'Test task description',
        'due_at' => '2026-1-08',
    ];
}

/**
 * Successful task creation
 */
test('creates a task successfully', function () {

    Sanctum::actingAs($this->user);

    // cache must be cleared
    $this->cacheMock
        ->shouldReceive('clearTasksCache')
        ->once()
        ->with(Mockery::type(Task::class));

    // success log must be written
    $this->loggerMock
        ->shouldReceive('info')
        ->once();

    $response = postJson('/api/v1/tasks', validTaskPayload());

    $response->assertStatus(201);

    // ensure task is stored in database
    expect(Task::count())->toBe(1);

    // ensure creator is attached to task
    $task = Task::first();
    expect($task->users->contains($this->user))->toBeTrue();
});

/**
 * User without permission cannot create task
 */
test('does not allow creating task without permission', function () {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = postJson('/api/v1/tasks', validTaskPayload());

    $response->assertStatus(403);
});

/**
 * Guest user cannot create task
 */
test('does not allow guest to create task', function () {

    $response = postJson('/api/v1/tasks', validTaskPayload());

    $response->assertStatus(401);
});

/**
 * Validation errors title
 */
test('returns validation error when title is missing', function () {

    Sanctum::actingAs($this->user);

    $payload = validTaskPayload();
    unset($payload['title']);

    $response = postJson('/api/v1/tasks', $payload);

    $response->assertStatus(422);
});

/**
 * Validation errors description
 */
test('returns validation error when description is missing', function () {

    Sanctum::actingAs($this->user);

    $payload = validTaskPayload();
    unset($payload['description']);

    $response = postJson('/api/v1/tasks', $payload);

    $response->assertStatus(422);
});

/**
 * Validation errors due_at
 */
test('returns validation error when due_at is missing', function () {

    Sanctum::actingAs($this->user);

    $payload = validTaskPayload();
    unset($payload['due_at']);

    $response = postJson('/api/v1/tasks', $payload);

    $response->assertStatus(422);
});

/**
 * during task creation error
 */
test('when exception happens during task creation', function () {

    Sanctum::actingAs($this->user);

    // Simulate a database failure by throwing an exception when the model is being saved
    Task::saving(fn() => throw new Exception('DB error'));

    $this->loggerMock
        ->shouldReceive('error')
        ->once();

    $response = postJson('/api/v1/tasks', validTaskPayload());

    $response->assertStatus(500);
});
