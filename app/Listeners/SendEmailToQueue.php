<?php

namespace App\Listeners;

use App\Events\EmailRequested;
use App\Jobs\SendEmailJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendEmailToQueue implements ShouldQueue
{
    public function handle(EmailRequested $event)
    {
        $emailData = [
            'recipient' => $event->user->email,
            'template' => $event->emailTemplate,
            'data' => [],
        ];

        if ($event->emailTemplate == 'email_verification_otp') {
            $emailData['data'] = [
                'name' => $event->user->name,
                'otp' => $event->user->email_verification_otp
            ];
        }
        else {
            Log::error("Invalid email type: {$event->emailTemplate} for user ID: {$event->user->id}");
            throw new \InvalidArgumentException("Invalid email type: {$event->emailTemplate}");
        }

        Log::info("Sending {$event->emailTemplate} email to user ID: {$event->user->id}");

        SendEmailJob::dispatch($emailData);
    }
}
