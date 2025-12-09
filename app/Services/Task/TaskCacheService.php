<?php

namespace App\Services\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class TaskCacheService
{
    private int $ttl = 60; // 60 seconds

    private function userTasksCacheKey($userId)
    {
        return "user_{$userId}_tasks";
    }

    private function adminTasksCacheKey()
    {
        return "admin_tasks";
    }

    /** ------------------------------------------------ */
    /** ----------------- USER CACHE ------------------- */
    /** ------------------------------------------------ */

    public function rememberUserTasks(User $user)
    {
        return cache()->remember($this->userTasksCacheKey($user->id), $this->ttl, function () use ($user) {
            return $user->tasks()->paginate(5);
        });
    }

    //Anyone who has this task should clear tasksCache.
    public function clearTasksCache($task)
    {
        foreach ($task->users as $user) {
            Cache::forget($this->userTasksCacheKey($user->id));
        }

        //clear admin tasks cache
        $this->clearAdminCache();
    }

    /** ------------------------------------------------ */
    /** ----------------- ADMIN CACHE ------------------- */
    /** ------------------------------------------------ */

    public function rememberAdminTasks()
    {
        return cache()->remember($this->adminTasksCacheKey(), $this->ttl, function () {
            return Task::with('users')->paginate(5);
        });
    }

    public function clearAdminCache()
    {
        Cache::forget($this->adminTasksCacheKey());
    }
}
