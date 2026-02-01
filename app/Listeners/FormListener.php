<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FormRequestEvent;
use App\Models\User;
use App\Notifications\FeedbackNotification;
use App\Services\Forms\Enum\FormType;
use App\Services\Notifications\Enum\EventType;
use App\Services\Notifications\NotificationService;

final readonly class FormListener
{
    public function __construct(public NotificationService $service) {}

    public function handle(FormRequestEvent $event): void
    {
        $eventType = match ($event->form->getType()) {
            FormType::Feedback => EventType::Feedback,
        };

        if ($eventType === null) {
            return;
        }

        /** @var User $user */
        foreach ($this->service->getAuthSubscribersList($eventType) as $user) {
            $user->notify(new FeedbackNotification($event->form));
        }
    }
}
