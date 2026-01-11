<?php

declare(strict_types=1);

namespace App\Events;

use App\Services\Email\Enum\EmailTypeEnum;
use App\Services\Forms\Enum\FormTypeEnum;
use App\Services\Forms\FormInterface;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FormRequestEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public FormInterface $form) {}
}
