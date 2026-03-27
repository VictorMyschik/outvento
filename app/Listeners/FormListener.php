<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FormRequestEvent;
use App\Models\User;
use App\Notifications\FeedbackNotification;
use App\Services\Forms\Enum\FormType;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Notifications\ServiceNotificationService;

final readonly class FormListener
{
    public function __construct(public ServiceNotificationService $service) {}

    public function handle(FormRequestEvent $event): void
    {
        $eventType = match ($event->form->getType()) {
            FormType::Feedback => ServiceEvent::Feedback,
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
