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
use App\Services\Logging\LogInterface;
use App\Services\Task\TaskCacheService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class TaskController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    private TaskCacheService $cacheService;
    private $logger;

    public function __construct(TaskCacheService $cacheService, LogInterface $logger)
    {
        $this->cacheService = $cacheService;
        $this->logger = $logger;
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

            return ApiResponse::collection(
                resourceCollection: TaskResource::collection($tasks)
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
        try {
            $task = Task::create([
                ...$request->validated(),
                'creator_id' => $request->user()->id,
            ]);

            $task->users()->attach($request->user()->id);

            $this->cacheService->clearTasksCache($task);

            $this->logger->info('Task created', [
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
            ]);

            return ApiResponse::created(
                data: new TaskResource($task),
                message: 'Task created successfully.'
            );

        } catch (\Exception $e) {

            $this->logger->error('Task creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return ApiResponse::error(
                message: 'Something went wrong while creating task.',
                status: 500
            );
        }
    }

    /**
     * show the specified task.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return ApiResponse::collection(
            resourceCollection: new TaskResource($task->load('users'))
        );
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateRequest $request, Task $task)
    {
        $this->authorize('update', $task);
        try {
            $task->update($request->validated());

            $this->cacheService->clearTasksCache($task);

            $this->logger->info('Task updated', [
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
            ]);

            return ApiResponse::updated(
                data: new TaskResource($task),
                message: 'Task updated successfully.'
            );

        } catch (\Exception $e) {

            $this->logger->error('Task update failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return ApiResponse::error(
                message: 'Something went wrong while updating task.',
                status: 500
            );
        }
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Request $request, Task $task)
    {
        $user = $request->user();
        $this->authorize('delete', $task);
        try {
            $this->cacheService->clearTasksCache($task);

            $task->delete();

            $this->logger->info('Task deleted', [
                'task_id' => $task->id,
                'user_id' => $user->id,
            ]);

            return ApiResponse::deleted(
                message: 'Task deleted successfully.'
            );

        } catch (\Exception $e) {

            $this->logger->error('Task deletion failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return ApiResponse::error(
                message: 'Something went wrong while deleting task.',
                status: 500
            );
        }
    }


    //update task status to complete or pending
    public function updateStatus(UpdateStatusRequest $request, Task $task)
    {
        $this->authorize('updateStatus', $task);
        try {
            $validated = $request->validated();

            $task->status = $validated['status'];
            $task->completed_at = $validated['status'] === TaskStatus::Completed->value ? now() : null;
            $task->save();

            $this->cacheService->clearTasksCache($task);

            $this->logger->info('Task status updated', [
                'task_id' => $task->id,
                'status' => $validated['status'],
                'user_id' => $request->user()->id,
            ]);

            return ApiResponse::updated(
                data: new TaskResource($task),
                message: 'Task status updated successfully.'
            );

        } catch (\Exception $e) {

            $this->logger->error('Task status update failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return ApiResponse::error(
                message: 'Something went wrong while updating task status.',
                status: 500
            );
        }
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
            return ApiResponse::collection(
                resourceCollection: TaskResource::collection($tasks)
            );
        }

        //search and filters
        $tasks = Task::search($search)->filterStatus($status)->paginate(10);

        return ApiResponse::collection(
            resourceCollection: TaskResource::collection($tasks)
        );

    }

}
