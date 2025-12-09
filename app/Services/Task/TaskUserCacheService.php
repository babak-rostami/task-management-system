<?php

namespace App\Services\Task;

use App\Models\Task;
use Illuminate\Support\Facades\Cache;

class TaskUserCacheService
{
    private int $ttl = 60; // 60 seconds

    private function taskUsersCacheKey($taskId)
    {
        return "task:{$taskId}:users";
    }

    public function rememberTaskUsers(Task $task)
    {
        return cache()->remember($this->taskUsersCacheKey($task->id), $this->ttl, function () use ($task) {
            return $task->users;
        });
    }

    public function clearTaskUsersCache($taskId)
    {
        Cache::forget($this->taskUsersCacheKey($taskId));
    }

}
