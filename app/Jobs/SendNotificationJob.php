<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly int    $userId,
        public readonly string $type,
        public readonly string $title,
        public readonly string $message,
        public readonly array  $data = []
    ) {}

    public function handle(NotificationService $notifier): void
    {
        $user = User::find($this->userId);
        if ($user) {
            $notifier->send($user, $this->type, $this->title, $this->message, $this->data);
        }
    }
}
