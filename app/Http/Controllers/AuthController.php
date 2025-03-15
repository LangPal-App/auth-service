<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;

use App\Models\User;
use App\Http\Resources\UserResource;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name'  => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password'  => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return $this->success(
            'User registered successfully',
            [
                'user' => new UserResource($user),
                'token' => $token
            ],
            201
        );
    }
}
