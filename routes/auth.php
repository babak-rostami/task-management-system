<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisterController::class, 'store'])
    ->middleware('api_guest:sanctum')
    ->name('register');

Route::post('/login', [LoginController::class, 'store'])
    ->middleware('api_guest:sanctum')
    ->name('login');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('logout');
