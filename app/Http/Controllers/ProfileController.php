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
        return $this->success(__('messages.success'), new UserResource($user));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $user->name = $request->input('name');
        $user->save();

        return $this->success(__('messages.profile_updated'), new UserResource($user));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8',
        ]);

        $user = auth()->user();

        if (!\Hash::check($request->input('currentPassword'), $user->password)) {
            throw new HttpException(422, __('messages.current_password_incorrect'));
        }

        $user->password = bcrypt($request->input('newPassword'));
        $user->save();

        return $this->success(__('messages.password_updated'));
    }

    public function uploadProfileImage(Request $request)
    {
        $request->validate([
            'profileImage' => 'required|file|image|max:5120',
        ]);

        $user = auth()->user();

        $path = saveInputFile($request->file('profileImage'), 'profiles/' . $user->id);

        $user->profile_image = url($path);
        $user->save();

        return $this->success(__('messages.profile_picture_uploaded'), [
            'profileImage' => $user->profile_image
        ]);
    }
}
