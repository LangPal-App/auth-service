<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;

use Illuminate\Support\Facades\Hash;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function createUser(): User
    {
        $password = 'P@ssword12';

        $user = User::factory()->unverified()->create([
            'username' => 'testuser',
            'name'  => 'Test',
            'email' => 'test@test.com',
            'password'  => Hash::make('P@ssword12'),
        ]);

        $user->plain_password = $password;

        return $user;
    }

    protected function createVerifiedUser(): User
    {
        $password = 'P@ssword12';

        $user = User::factory()->create([
            'username' => 'testuser',
            'name'  => 'Test',
            'email' => 'test@test.com',
            'password'  => Hash::make($password),
        ]);

        $user->plain_password = $password;

        return $user;
    }
}
