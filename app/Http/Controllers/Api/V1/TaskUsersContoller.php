<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserTask\AddUserToTaskRequest;
use App\Http\Requests\UserTask\RemoveUserFromTaskRequest;
use App\Http\Resources\UserResource;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class TaskUsersContoller extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:task.users', only: ['index']),
            new Middleware('permission:assign.task.user', only: ['store']),
            new Middleware('permission:unassign.task.user', only: ['destroy']),
        ];
    }

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

        $users->attach($request->user_id);

        return response()->json([
            'message' => 'User added to task successfully.',
            'users' => UserResource::collection($task->users),
        ]);
    }

    public function destroy(Request $request, Task $task, $user_id)
    {
        $this->authorize('unassignUser', $task);

        if ($user_id == $request->user()->id) {
            return response()->json([
                'error' => 'task creator cannot be deleted.',
            ], 403);
        }

        $users = $task->users();

        if (!$users->find($user_id)) {
            return response()->json([
                'error' => 'This user does not exist in this task.',
            ], 404);
        }

        $users->detach($user_id);

        return response()->json([
            'message' => 'User removed from task successfully.',
            'users' => UserResource::collection($task->users),
        ]);
    }
}
