<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Task\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreRequest;
use App\Http\Requests\Task\UpdateRequest;
use App\Http\Requests\Task\UpdateStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        $tasks = $user->tasks()->paginate(10);
        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $data['creator_id'] = $request->user()->id;

        $task = Task::create($data);

        return new TaskResource($task);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        if (request()->user()->cannot('view', $task)) {
            abort(403, 'access forbidden');
        }
        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Task $task): TaskResource
    {
        if (request()->user()->cannot('update', $task)) {
            abort(403, 'access forbidden');
        }

        $data = $request->validated();

        $task->update($data);

        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        if (request()->user()->cannot('delete', $task)) {
            abort(403, 'access forbidden');
        }

        $task->delete();

        return response()->noContent();
    }

    public function updateStatus(UpdateStatusRequest $request, Task $task)
    {
        $validated = $request->validated();

        $task->status = $validated['status'];

        //completed_at
        $task->completed_at = $validated['status'] === TaskStatus::Completed->value
            ? now()
            : null;

        $task->save();

        return new TaskResource($task);
    }

}
