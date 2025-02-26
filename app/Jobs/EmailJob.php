<?php

namespace App\Jobs;

use App\Services\Email\EmailService;
use App\Services\Email\Enum\EmailTypeEnum;
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

    public function __construct(public string $to, public Mailable $email, public EmailTypeEnum $type) {}

    public function handle(EmailService $service): void
    {
        $service->send($this->to, $this->email, $this->type);
    }
}
