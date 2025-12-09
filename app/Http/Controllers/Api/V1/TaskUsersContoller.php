<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserTask\AddUserToTaskRequest;
use App\Http\Requests\UserTask\RemoveUserFromTaskRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\Task;
use App\Services\Task\TaskUserCacheService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;

class TaskUsersContoller extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    private TaskUserCacheService $cacheService;

    public function __construct(TaskUserCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
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

        $users = $this->cacheService->rememberTaskUsers($task);

        return ApiResponse::collection(
            resourceCollection: UserResource::collection($users),
        );


    }

    public function store(AddUserToTaskRequest $request, Task $task)
    {
        $this->authorize('assignUser', $task);

        $users = $task->users();

        if ($users->find($request->user_id)) {
            return ApiResponse::error(
                message: 'This user already exists in task.',
                status: 409
            );
        }

        $users->attach($request->user_id);

        $this->cacheService->clearTaskUsersCache($task->id);

        return ApiResponse::created(
            data: UserResource::collection($task->users),
            message: 'User added to task successfully.'
        );
    }

    public function destroy(Request $request, Task $task, $user_id)
    {
        $this->authorize('unassignUser', $task);

        if ($user_id == $request->user()->id) {
            return ApiResponse::error(
                message: 'Task creator cannot be deleted.',
                status: 403
            );
        }

        $users = $task->users();

        if (!$users->find($user_id)) {
            return ApiResponse::error(
                message: 'This user does not exist in this task.',
                status: 404
            );
        }

        $users->detach($user_id);

        $this->cacheService->clearTaskUsersCache($task->id);

        return ApiResponse::deleted(
            message: 'User removed from task successfully.'
        );
    }
}
