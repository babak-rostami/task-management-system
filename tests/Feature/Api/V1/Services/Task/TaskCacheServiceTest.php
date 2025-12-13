<?php

use App\Models\Task;
use App\Models\User;
use App\Services\Task\TaskCacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new TaskCacheService();
});

test('caches user tasks', function () {

    $user = User::factory()->create();

    Task::factory()->count(3)->create();

    // should hit database and store in cache
    $this->service->rememberUserTasks($user);

    // cache key should exist
    expect(cache()->has("user_{$user->id}_tasks"))->toBeTrue();
});

test('uses cached user tasks on second call', function () {

    $user = User::factory()->create();

    Task::factory()->count(2)->create();

    $this->service->rememberUserTasks($user);

    // Remove all tasks from DB
    Task::query()->delete();

    // Second call → should come from cache
    $cachedResult = $this->service->rememberUserTasks($user);

    expect($cachedResult->total())->toBe(2);
});

test('clears tasks cache for all task users', function () {

    $task = Task::factory()->create();

    $users = $task->users;

    foreach ($users as $user) {
        Cache::put("user_{$user->id}_tasks", 'cached', 60);
    }

    Cache::put('admin_tasks', 'cached', 60);

    $this->service->clearTasksCache($task);

    foreach ($users as $user) {
        expect(cache()->has("user_{$user->id}_tasks"))->not()->toBeTrue();
    }

    expect(cache()->has('admin_tasks'))->not()->toBeTrue();
});

test('caches admin tasks', function () {

    Task::factory()->count(2)->create();

    $this->service->rememberAdminTasks();

    expect(cache()->has('admin_tasks'))->toBeTrue();
});
