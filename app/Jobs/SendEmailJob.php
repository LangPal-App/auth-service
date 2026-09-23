<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $emailData;

    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    public function handle()
    {
        try {
            Queue::connection('rabbitmq')->pushRaw(json_encode($this->emailData), 'email_queue');
        } catch (\Throwable $e) {
            Log::error('Failed to push email job to rabbitmq: ' . $e->getMessage(), [
                'exception' => $e,
                'emailData' => $this->emailData
            ]);
        }
    }
}
