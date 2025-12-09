<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Task\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreRequest;
use App\Http\Requests\Task\UpdateRequest;
use App\Http\Requests\Task\UpdateStatusRequest;
use App\Http\Resources\TaskResource;
use App\Http\Responses\ApiResponse;
use App\Models\Task;
use App\Services\Task\TaskCacheService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class TaskController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    private TaskCacheService $cacheService;

    public function __construct(TaskCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    public static function middleware(): array
    {
        return [
            new Middleware('permission:my.tasks', only: ['index']),
            new Middleware('permission:create.task', only: ['store']),
            new Middleware('permission:update.task', only: ['update']),
            new Middleware('permission:read.task', only: ['show']),
            new Middleware('permission:delete.task', only: ['destroy']),
            new Middleware('permission:update.task.status', only: ['updateStatus']),
            new Middleware('permission:all.tasks', only: ['indexAdmin']),
        ];
    }

    /**
     * Display User Tasks
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $search = $request->search;
        $status = $request->status;
        $page = $request->page ?? 1;

        $hasFilter = $search || $status;

        //if there is no filter and page == 1
        if (!$hasFilter && $page == 1) {
            $tasks = $this->cacheService->rememberUserTasks($user);
            return ApiResponse::success(
                data: TaskResource::collection($tasks)
            );

        }

        //search and filters
        $tasks = $user->tasks()->search($search)->filterStatus($status)->paginate(10);

        return ApiResponse::collection(
            resourceCollection: TaskResource::collection($tasks)
        );

    }

    /**
     * Store a new task.
     */
    public function store(StoreRequest $request)
    {
        $task = Task::create([
            ...$request->validated(),
            'creator_id' => $request->user()->id,
        ]);

        $task->users()->attach($request->user()->id);

        $this->cacheService->clearTasksCache($task);

        return ApiResponse::created(
            data: new TaskResource($task),
            message: 'Task created successfully.'
        );

    }

    /**
     * show the specified task.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return ApiResponse::success(
            data: new TaskResource($task)
        );
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        $this->cacheService->clearTasksCache($task);

        return ApiResponse::updated(
            data: new TaskResource($task),
            message: 'Task updated successfully.'
        );
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $this->cacheService->clearTasksCache($task);

        $task->delete();

        return ApiResponse::deleted(
            message: 'Task deleted successfully.'
        );
    }

    //update task status to complete or pending
    public function updateStatus(UpdateStatusRequest $request, Task $task)
    {
        $this->authorize('updateStatus', $task);

        $validated = $request->validated();

        $task->status = $validated['status'];

        //task completed_at
        $task->completed_at = $validated['status'] === TaskStatus::Completed->value ? now() : null;

        $task->save();

        $this->cacheService->clearTasksCache($task);

        return ApiResponse::updated(
            data: new TaskResource($task),
            message: 'Task status updated successfully.'
        );
    }

    /**
     * get all tasks for admin
     */
    public function indexAdmin(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $page = $request->page ?? 1;

        $hasFilter = $search || $status;

        if (!$hasFilter && $page == 1) {
            $tasks = $this->cacheService->rememberAdminTasks();
            return ApiResponse::success(
                data: TaskResource::collection($tasks)
            );
        }

        //search and filters
        $tasks = Task::search($search)->filterStatus($status)->paginate(10);

        return ApiResponse::collection(
            resourceCollection: TaskResource::collection($tasks)
        );

    }

}
