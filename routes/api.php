<?php

use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TaskUsersContoller;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::prefix('v1')->group(function () {
        //task crud
        Route::apiResource('tasks', TaskController::class);
        //admin can see all tasks
        Route::get('admin/tasks', [TaskController::class, 'indexAdmin']);

        //update task status
        Route::post('tasks/{task:id}/status', [TaskController::class, 'updateStatus']);

        //assign and remove user from task
        Route::apiResource('tasks/{task:id}/users', TaskUsersContoller::class)
            ->except(['show', 'update']);

        Route::get('users', [UserController::class, 'index']);
    });
});