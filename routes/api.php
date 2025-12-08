<?php

use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

require __DIR__ . '/auth.php';

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::apiResource('tasks', TaskController::class);

        Route::post('tasks/{task:id}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    });
});
