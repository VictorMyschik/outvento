<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\Notifications\NotificationRepositoryInterface;

readonly class LogNotificationFailed
{
    public function __construct(private NotificationRepositoryInterface $repository) {}

    public function handle(object $event): void
    {
        $message = $event->message;

        $this->repository->setEmailLog([
            'type'    => $message->getHeaders()->get('X-Notification-Key')?->getBodyAsString(),
            'email'   => $message->getTo()[0]->getAddress(),
            'subject' => $message->getSubject(),
            'sl'      => json_encode($message->getHtmlBody() ?? $message->getTextBody()),
            'status'  => false,
            'error'   => $event->exception->getMessage(),
        ]);
    }
}
