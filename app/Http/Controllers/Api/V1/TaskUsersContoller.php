<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserTask\AddUserToTaskRequest;
use App\Http\Requests\UserTask\RemoveUserFromTaskRequest;
use App\Http\Resources\UserResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class TaskUsersContoller extends Controller
{
    use AuthorizesRequests;

    public function index(Task $task)
    {
        $this->authorize('viewUsers', $task);
        return response()->json([
            'message' => 'task users',
            'data' => UserResource::collection($task->users)
        ]);
    }

    public function store(AddUserToTaskRequest $request, Task $task)
    {
        $this->authorize('assignUser', $task);

        $users = $task->users();

        if ($users->find($request->user_id)) {
            return response()->json([
                'error' => 'This user already exist in task.',
            ], 403);
        }

        // Attach
        $users->syncWithoutDetaching($request->user_id);

        return response()->json([
            'message' => 'User added to task successfully.',
            'users' => UserResource::collection($task->users),
        ]);
    }

    public function destroy(RemoveUserFromTaskRequest $request, Task $task)
    {
        $this->authorize('unassignUser', $task);

        if ($request->user_id == $request->user()->id) {
            return response()->json([
                'error' => 'task creator cannot be deleted.',
            ], 403);
        }

        $users = $task->users();

        if (!$users->find($request->user_id)) {
            return response()->json([
                'error' => 'This user does not exist in this task.',
            ], 404);
        }

        $users->detach($request->user_id);

        return response()->json([
            'message' => 'User removed from task successfully.',
            'users' => UserResource::collection($task->users),
        ]);
    }
}
