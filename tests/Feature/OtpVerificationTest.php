<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Tests\TestCase;

class OtpVerificationTest extends TestCase
{
    public function test_user_can_verify_email_with_correct_otp()
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/verify-otp', [
            'email' => $user->email,
            'otp' => $user->email_verification_otp,
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'user',
                        'token'
                    ],
                    'errors'
                ]);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_user_cannot_verify_email_with_incorrect_otp()
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/verify-otp', [
            'email' => $user->email,
            'otp' => 'wrong_otp',
        ]);

        $response->assertStatus(400);
    }

    public function test_user_cannot_verify_email_with_expired_otp()
    {
        $user = $this->createUser();
        
        $user->email_verification_otp_expires_at = now()->subMinutes(1);
        $user->save();

        $response = $this->postJson('/api/verify-otp', [
            'email' => $user->email,
            'otp' => $user->email_verification_otp,
        ]);

        $response->assertStatus(400);
    }

    public function test_user_can_resend_otp_after_cooldown_period()
    {
        $user = $this->createUser();

        $user->email_verification_otp_created_at = now()->subMinutes(1);
        $user->save();

        $response = $this->postJson('/api/resend-otp', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
    }

    public function test_user_cannot_resend_otp_before_cooldown_period()
    {
        $user = $this->createUser();

        $user->update(['otp_requested_at' => now()->subSeconds(30)]);

        $response = $this->postJson('/api/resend-otp', [
            'email' => $user->email,
        ]);

        $response->assertStatus(429);
    }

    public function test_user_cannot_resend_otp_too_many_times()
    {
        $user = $this->createUser();

        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/resend-otp', [
                'email' => $user->email,
            ]);
        }

        $response = $this->postJson('/api/resend-otp', [
            'email' => $user->email,
        ]);

        $response->assertStatus(429);
    }
}