<?php

namespace App\Jobs;

use App\Services\Notifications\Enum\EventType;
use App\Services\Notifications\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public string $to, public Mailable $email, public EventType $type) {}

    public function handle(NotificationService $service): void
    {
        $service->customEmailNotify($this->to, $this->email, $this->type);
    }
}
