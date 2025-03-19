<?php

namespace App\Http\Requests;

class LoginRequest extends BaseRequest
{
    public function authorize()
    {
        return !$this->user();
    }

    public function rules()
    {
        return [
            'email' => 'required|string|email|max:64',
            'password' => 'required|string|min:8',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ];
    }
}
