<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LoginController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $user = $request->user();
        $token = $user->createToken('main')->plainTextToken;

        return ApiResponse::success(
            data: [
                'token' => $token
            ],
            message: 'You are logged in.',
        );

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        return ApiResponse::success(
            message: 'You are logged out.',
        );
    }
}
