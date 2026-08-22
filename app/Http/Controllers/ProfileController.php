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

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $user->name = $request->input('name');
        $user->save();

        return $this->success('Profile updated successfully', new UserResource($user));
    }
}
