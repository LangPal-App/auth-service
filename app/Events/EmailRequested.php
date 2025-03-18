<?php
namespace App\Events;

use App\Models\User;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailRequested
{
    use Dispatchable, SerializesModels;

    public User $user;
    public string $emailTemplate;

    public function __construct(User $user, string $emailTemplate)
    {
        $this->user = $user;
        $this->emailTemplate = $emailTemplate;
    }
}
