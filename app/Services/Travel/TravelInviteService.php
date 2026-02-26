<?php

declare(strict_types=1);

namespace App\Services\Travel;

use App\Models\Travel\Travel;
use App\Models\TravelInvite;
use App\Models\User;
use App\Notifications\Service\TravelInviteNotification;
use App\Services\Notifications\DTO\SystemEmailNotificationDto;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Notifications\Enum\SystemEvent;
use App\Services\Notifications\SystemNotificationService;
use App\Services\Travel\DTO\TravelInviteDto;
use App\Services\User\UserService;

final readonly class TravelInviteService
{
    public function __construct(
        private UserService                     $userService,
        private TravelInviteRepositoryInterface $repository,
        private SystemNotificationService       $systemNotificationService,
    ) {}

    public function invite(Travel $travel, string $address): void
    {
        $user = $this->userService->findUser($address);

        $this->repository->saveInvite(
            travelId: $travel->id,
            email: $address,
            userId: $user?->id,
        );

        if ($user) {
            $this->sendUserTravelInvite($user, $travel);

            return;
        }

        $this->systemNotificationService->send(
            new SystemEmailNotificationDto(
                address: $address,
                eventType: SystemEvent::TravelInvite,
                channel: NotificationChannel::Email,
                data: [
                    'dto' => new TravelInviteDto(
                        activities: $travel->getActivitiesByLanguage($travel->getOwner()->getLanguage()),
                        countryLabels: $travel->getCountriesByLanguage($travel->getOwner()->getLanguage()),
                        confirmationUrl: $travel->getPublicId(),
                    )
                ],
            )
        );
    }

    public function reSendTravelInvite(int $inviteId): void
    {
        $invite = TravelInvite::loadByOrDie($inviteId);

        if ($user = $invite->getUser()) {
            $this->sendUserTravelInvite($user, $invite->getTravel());
        }
    }

    public function getListByTravel(int $travelId): array
    {
        return $this->repository->getListByTravel($travelId);
    }

    public function getListByUser(int $userId): array
    {
        return $this->repository->getListByUser($userId);
    }

    public function removeTravelInvite(int $inviteId): void
    {
        $this->repository->removeTravelInvite($inviteId);
    }

    private function sendUserTravelInvite(User $user, Travel $travel): void
    {
        if ($this->repository->isMute($user->id, ServiceEvent::Invite)) {
            return;
        }

        $user->notify(new TravelInviteNotification(new TravelInviteDto(
            activities: $travel->getActivitiesByLanguage($user->getLanguage()),
            countryLabels: $travel->getCountriesByLanguage($user->getLanguage()),
            confirmationUrl: $travel->getPublicId(),
        )));
    }
}