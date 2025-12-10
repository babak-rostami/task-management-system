<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    //get users
    public function index()
    {
        $users = User::paginate(50);
        return ApiResponse::collection(
            resourceCollection: UserResource::collection($users)
        );
    }

}
