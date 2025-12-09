<?php

namespace App\Models;

use App\Enums\Task\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'due_at', 'creator_id', 'status', 'completed_at'];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => TaskStatus::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

}
