<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Resources\UserResource;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        return $this->success('Success', new UserResource($user));
    }
}
