<?php

namespace Tests\Feature;

use Tests\TestCase;

use Illuminate\Support\Facades\Log;

class LoginTest extends TestCase
{
    public function test_login_success(): void
    {
        $user = $this->createVerifiedUser();

        Log::shouldReceive('info')
                ->once()
                ->with('User logged in successfully', ['email' => $user->email]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $user->plain_password
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => ['user', 'token'],
                    'errors'
                ]);
    }

    public function test_case_sensitivity_is_neglected_in_email()
    {
        $user = $this->createVerifiedUser();

        Log::shouldReceive('info')
                ->once()
                ->with('User logged in successfully', ['email' => ucfirst($user->email)]);

        $response = $this->postJson('/api/login', [
            'email' => ucfirst($user->email),
            'password' => $user->plain_password
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => ['user', 'token'],
                    'errors'
                ]);
    }

    public function test_unverified_user_cannot_login()
    {
        $user = $this->createUser();

        Log::shouldReceive('warning')
                ->once()
                ->with('User login failed | Unverified user', ['email' => $user->email]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $user->plain_password
        ]);

        $response->assertStatus(403);
    }

    public function test_missing_email_and_password()
    {
        $response = $this->postJson('/api/login', []);
        $response->assertStatus(400)
                ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_wrong_email()
    {
        $user = $this->createVerifiedUser();
        $wrongEmail = 'wrong@test.com';
        
        Log::shouldReceive('warning')
                ->once()
                ->with('User login failed', ['email' => $wrongEmail]);

        $response = $this->postJson('/api/login', [
            'email' => $wrongEmail,
            'password' => $user->plain_password
        ]);

        $response->assertStatus(404);
    }

    public function test_wrong_password()
    {
        $user = $this->createVerifiedUser();
        
        Log::shouldReceive('warning')
                ->once()
                ->with('User login failed', ['email' => $user->email]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong_password'
        ]);

        $response->assertStatus(404);
    }
}
