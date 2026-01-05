<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FormRequestEvent;
use App\Services\Email\EmailService;
use App\Services\Email\Enum\EmailTypeEnum;
use App\Services\Forms\Enum\FormTypeEnum;

final readonly class FormEmailListener
{
    public function __construct(private EmailService $emailService) {}

    public function handle(FormRequestEvent $event): void
    {
        $this->emailService->createNewEmail($this->getEmailType($event->form->getType()), $event);
    }

    public function getEmailType(FormTypeEnum $type): EmailTypeEnum
    {
        return match ($type) {
            FormTypeEnum::Feedback => EmailTypeEnum::Feedback,
        };
    }
}
