<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserTask\AddUserToTaskRequest;
use App\Http\Requests\UserTask\RemoveUserFromTaskRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\Task;
use App\Services\Logging\LogInterface;
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
    private $logger;

    public function __construct(TaskUserCacheService $cacheService, LogInterface $logger)
    {
        $this->cacheService = $cacheService;
        $this->logger = $logger;
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
        try {
            $this->authorize('viewUsers', $task);

            $users = $this->cacheService->rememberTaskUsers($task);

            return ApiResponse::collection(
                resourceCollection: UserResource::collection($users)
            );

        } catch (\Throwable $e) {
            $this->logger->error('Failed to load task users.', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error(
                message: 'Unable to load users.',
                status: 500
            );
        }
    }


    public function store(AddUserToTaskRequest $request, Task $task)
    {
        try {
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

            $this->logger->info('User assigned to task.', [
                'task_id' => $task->id,
                'assigned_user_id' => $request->user_id,
                'performed_by' => $request->user()->id,
            ]);

            return ApiResponse::created(
                data: UserResource::collection($task->users),
                message: 'User added to task successfully.'
            );

        } catch (\Throwable $e) {
            $this->logger->error('Failed to assign user to task.', [
                'task_id' => $task->id,
                'assigned_user_id' => $request->user_id,
                'performed_by' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error(
                message: 'Unable to add user.',
                status: 500
            );
        }
    }


    public function destroy(Request $request, Task $task, $user_id)
    {
        try {
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

            $this->logger->info('User removed from task.', [
                'task_id' => $task->id,
                'removed_user_id' => $user_id,
                'performed_by' => $request->user()->id,
            ]);

            return ApiResponse::deleted(
                message: 'User removed from task successfully.'
            );

        } catch (\Throwable $e) {
            $this->logger->error('Failed to remove user from task.', [
                'task_id' => $task->id,
                'removed_user_id' => $user_id,
                'performed_by' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error(
                message: 'Unable to remove user.',
                status: 500
            );
        }
    }

}
