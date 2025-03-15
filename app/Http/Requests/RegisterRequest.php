<?php

namespace App\Http\Requests;

class RegisterRequest extends BaseRequest
{
    public function authorize()
    {
        return !$this->user();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|min:3|max:32|regex:/^[a-zA-Z\s]+$/',
            'username' => 'required|string|min:3|max:32|alpha_dash|unique:users',
            'email' => 'required|string|email|max:64|unique:users',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => 'The name may only contain letters and spaces.',
            'username.required' => 'The username field is required.',
            'username.unique' => 'The username has already been taken.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ];
    }
}