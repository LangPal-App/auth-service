<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32);
            $table->string('username', 32);
            $table->string('email', 64)->unique();
            $table->string('profile_image')->nullable();
            $table->string('password');
            $table->string('email_verification_otp', 6)->nullable();
            $table->timestamp('email_verification_otp_created_at')->nullable();
            $table->timestamp('email_verification_otp_expires_at')->nullable();
            $table->unsignedTinyInteger('email_verification_otp_attempts')->default(0);
            $table->timestamp('email_verification_otp_blocked_until')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
