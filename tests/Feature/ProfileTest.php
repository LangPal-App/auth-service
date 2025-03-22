<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfileTest extends TestCase
{
    public function test_get_profile_success(): void
    {
        $user = $this->createUser();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        $response->assertStatus(200)
                ->assertJsonStructure(['message', 'data', 'errors'])
                ->assertJson([
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'email' => $user->email,
                        'bio' => $user->bio,
                        'photo' => $user->photo, 
                    ],
                    'errors' => []
                ]);
    }

    public function test_non_authenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/profile');
        $response->assertStatus(401)
                ->assertJson(['data' => []]);
    }

    public function test_user_cannot_get_profile_with_wrong_jwt(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->getJson('/api/profile');
        $response->assertStatus(401)
                ->assertJson(['data' => []]);
    }
}
