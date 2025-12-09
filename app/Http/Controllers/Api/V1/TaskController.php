<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Task\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreRequest;
use App\Http\Requests\Task\UpdateRequest;
use App\Http\Requests\Task\UpdateStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class TaskController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

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
    public function index()
    {
        $user = request()->user();
        $tasks = $user->tasks()->paginate(10);
        return TaskResource::collection($tasks);
    }

    /**
     * Store a new task.
     */
    public function store(StoreRequest $request): TaskResource
    {
        $data = $request->validated();
        $data['creator_id'] = $request->user()->id;

        $task = Task::create($data);
        $task->users()->attach($request->user()->id);

        return new TaskResource($task);
    }

    /**
     * show the specified task.
     */
    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateRequest $request, Task $task): TaskResource
    {
        $this->authorize('update', $task);

        $data = $request->validated();

        $task->update($data);

        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->noContent();
    }

    public function updateStatus(UpdateStatusRequest $request, Task $task): TaskResource
    {
        $this->authorize('updateStatus', $task);

        $validated = $request->validated();

        $task->status = $validated['status'];

        //completed_at
        $task->completed_at = $validated['status'] === TaskStatus::Completed->value
            ? now()
            : null;

        $task->save();

        return new TaskResource($task);
    }

    /**
     * get all tasks
     */
    public function indexAdmin()
    {
        $tasks = Task::paginate(10);
        return TaskResource::collection($tasks);
    }

}
