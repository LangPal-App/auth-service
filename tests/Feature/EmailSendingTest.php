<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Models\User;
use App\Events\EmailRequested;
use Illuminate\Support\Facades\Event;

use App\Listeners\SendEmailToQueue;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendEmailJob;

use Mockery;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class EmailSendingTest extends TestCase
{
    private $emailTemplate = 'email_verification_otp';

    public function test_email_requested_event_is_dispatched()
    {
        Event::fake();
        $user = $this->createUser();

        event(new EmailRequested($user, $this->emailTemplate));
    
        Event::assertDispatched(EmailRequested::class, function ($event) use ($user) {
            return $event->user == $user && $event->emailTemplate == $this->emailTemplate;
        });
    }

    public function test_send_email_to_queue_listener_dispatches_job()
    {
        Queue::fake();

        $user = $this->createUser();

        $event = new EmailRequested($user, $this->emailTemplate);
        $listener = new SendEmailToQueue();
        $listener->handle($event);

        Queue::assertPushed(SendEmailJob::class, function ($job) use ($user) {
            return $job->emailData['recipient'] == $user->email && $job->emailData['template'] == $this->emailTemplate;
        });
    }
}
