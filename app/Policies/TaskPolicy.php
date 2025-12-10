<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;

class TaskPolicy
{
    public function before(User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function view(User $user, Task $task): bool
    {
        return $this->belongsToTask($user, $task);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->isCreator($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->isCreator($user, $task);
    }

    //only task create can assignUser new user
    public function assignUser(User $user, Task $task): bool
    {
        return $this->isCreator($user, $task);
    }

    //only task create can unassignUser user
    public function unassignUser(User $user, Task $task): bool
    {
        return $this->isCreator($user, $task);
    }

    //can see task users?
    public function viewUsers(User $user, Task $task): bool
    {
        return $this->belongsToTask($user, $task);
    }

    //users in task can update task status to completed or pending
    public function updateStatus(User $user, Task $task): bool
    {
        return $this->belongsToTask($user, $task);
    }


    // Helpers
    private function belongsToTask(User $user, Task $task): bool
    {
        return $task->users()->where('users.id', $user->id)->exists();
    }

    private function isCreator(User $user, Task $task): bool
    {
        return $user->id === $task->creator_id;
    }
}
