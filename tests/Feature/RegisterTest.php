<?php

namespace Tests\Feature\Feature;

use App\Models\User;

use Tests\TestCase;

class RegisterTest extends TestCase
{
    public function test_register_success(): void
    {
        $valid_data = $this->getUserData();
        $response = $this->postJson('/api/register', $valid_data);
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'user',
                        'token'
                    ],
                    'errors'
                ]);
    }

    public function test_duplicate_email(): void
    {
        $user = $this->createUser();

        $data = $this->getUserData(['email' => $user->email]);

        $response = $this->postJson('/api/register',$data);
        $response->assertStatus(400)
                ->assertJsonValidationErrors('email');
    }

    public function test_missing_required_fields()
    {
        $response = $this->postJson('/api/register', []);
        $response->assertStatus(400)
                ->assertJsonValidationErrors(['username', 'email', 'password']);
    }

    public function test_invalid_email_format()
    {
        $data = $this->getUserData(['email' => 'invalid-email']);

        $response = $this->postJson('/api/register', $data);
        $response->assertStatus(400)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_weak_password()
    {
        $data = $this->getUserData(['password' => 'weak']);

        $response = $this->postJson('/api/register', $data);
        $response->assertStatus(400)
                ->assertJsonValidationErrors(['password']);
    }

    public function test_duplicate_username()
    {
        $user = $this->createUser();

        $data = $this->getUserData(['username' => $user->username]);

        $response = $this->postJson('/api/register', $data);
        $response->assertStatus(400)
                ->assertJsonValidationErrors('username');
    }

    public function test_empty_input_values()
    {
        $response = $this->postJson('/api/register', [
            'username' => '',
            'email' => '',
            'password' => '',
        ]);
        $response->assertStatus(400)
                ->assertJsonValidationErrors(['username', 'email', 'password']);
    }

    public function test_case_sensitivity_in_email()
    {
        $user = $this->createUser();

        $data = $this->getUserData([
            'email' => ucfirst($user->email)
        ]);

        $response = $this->postJson('/api/register', $data);
        $response->assertStatus(400)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_case_sensitivity_in_username()
    {
        $user = $this->createUser();

        $data = $this->getUserData([
            'username' => ucfirst($user->username)
        ]);

        $response = $this->postJson('/api/register', $data);
        $response->assertStatus(400)
                ->assertJsonValidationErrors('username');
    }

    public function test_special_characters_in_username()
    {
        $data = $this->getUserData(['username' => 'test@user!']);

        $response = $this->postJson('/api/register', $data);
        $response->assertStatus(400)
                ->assertJsonValidationErrors('username');
    }

    public function test_whitespace_in_username_and_email_are_trimmed_with_success_returned()
    {
        $valid_data = $this->getUserData();
        $data = $this->getUserData([
            'username' => '  ' . $valid_data['username'] . '  ',
            'email' => '  ' . $valid_data['email'] . '  '
        ]);
        
        $response = $this->postJson('/api/register', $data);

        $user = User::where('email', 'test@test.com')->first();

        $response->assertStatus(201);
        $this->assertEquals($user->username, $valid_data['username']);
        $this->assertEquals($user->email, $valid_data['email']);
    }

    public function test_whitespace_in_password_returns_error()
    {
        $data = $this->getUserData();
        $data['password'] = '  ' . $data['password'] . '  ';
        
        $response = $this->postJson('/api/register', $data);
        $response->assertStatus(400)
                ->assertJsonValidationErrors(['password']);
    }

    public function test_long_input_values()
    {
        $longString = str_repeat('a', 256);
        $data = $this->getUserData([
            'name' => $longString,
            'username' => $longString,
            'email' => $longString . '@example.com',
            'password' => $longString,
        ]);
        $response = $this->postJson('/api/register', $data);
        $response->assertStatus(400)
                ->assertJsonValidationErrors(['name', 'username', 'email', 'password']);
    }

    private function getUserData($override = [])
    {
        $valid_data = [
            'name' => 'Test Name',
            'username' => 'test_username',
            'email' => 'test@test.com',
            'password' => 'P@ssword12'
        ];

        return array_merge($valid_data, $override);
    }
}
