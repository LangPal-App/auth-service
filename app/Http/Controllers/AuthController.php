<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;

use App\Models\User;
use App\Http\Resources\UserResource;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Events\EmailRequested;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name'  => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password'  => Hash::make($request->password),
        ]);

        $this->sendOtpVerification($user);

        return $this->success('User registered successfully. Please check your email for verification.', [], 201);
    }

    public function verifyOtp(Request $request)
    {
        $user = User::where([
            'email' => $request->email,
            'email_verification_otp' => $request->otp,
        ])->first();

        if (!$user) {
            return $this->failed('Wrong OTP', [], 400);
        }

        if ($user->email_verified_at) {
            $user->email_verification_otp_blocked_until = Carbon::now()->addDay();
            $user->save();
            return $this->failed('Sorry something went wrong');
        }

        if ($this->otpExpired($user)) {
            return $this->failed('OTP has expired. Please request a new one.', [], 400);
        }

        $user->email_verified_at = now();
        $user->save();
        $token = JWTAuth::fromUser($user);

        return $this->success("User is verified successfully.", [
            'user' => new UserResource($user),
            'token' => $token
        ]);
    }

    public function resendOtp(Request $request)
    {
        $user = User::whereEmail($request->email)->first();

        if (!$user) {
            return $this->failed('Email not found.', [], 404);
        }

        if ($user->email_verified_at) {
            $user->email_verification_otp_blocked_until = Carbon::now()->addDay();
            $user->save();
            return $this->failed('Sorry something went wrong');
        }

        if ($this->isUserOtpBlocked($user)) {
            return $this->failed('You are blocked for 24 hours. Please try again tomorrow.', [], 400);
        }

        if ($user->email_verification_otp_attempts == 3) {
            $user->email_verification_otp_blocked_until = now()->addDay();
            $user->email_verification_otp_attempts = 0;
            $user->save();
            return $this->failed('Too many OTP resend attempts. You have been blocked for 24 hours. Please try again tomorrow.', [], 400);
        }

        if (!$this->otpCoolDownPassed($user)) {
            return $this->failed('Please wait 1 minute before resending OTP', [], 429);
        }

        $this->sendOtpVerification($user);

        return $this->success('OTP has been sent to your email.');
    }

    private function sendOtpVerification(User $user)
    {
        $user->email_verification_otp = generateRandomNumbers(6);
        $user->email_verification_otp_created_at = Carbon::now();
        $user->email_verification_otp_expires_at = Carbon::now()->addMinutes(10);
        $user->email_verification_otp_attempts = $user->email_verification_otp_attempts + 1;
        $user->save();

        event(new EmailRequested($user, 'email_verification_otp'));
    }

    private function isUserOtpBlocked(User $user): bool
    {
        return $user->email_verification_otp_blocked_until && now()->lessThan($user->email_verification_otp_blocked_until);
    }

    private function otpExpired(User $user): bool
    {
        return now()->greaterThan($user->email_verification_otp_expires_at);
    }

    private function otpCoolDownPassed(User $user): bool
    {
        $coolDownTime = Carbon::parse($user->email_verification_otp_created_at)->addMinutes(1);
        return now()->greaterThan($coolDownTime);
    }
}
