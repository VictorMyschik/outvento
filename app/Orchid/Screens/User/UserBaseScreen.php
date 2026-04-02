<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Models\User;
use App\Services\Conversations\ConversationService;
use App\Services\Promo\SubscriptionService;
use App\Services\Travel\TravelCommentService;
use App\Services\Travel\TravelInviteService;
use App\Services\Travel\TravelService;
use App\Services\User\AuthService;
use App\Services\User\UserLocationService;
use App\Services\User\UserService;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;

class UserBaseScreen extends Screen
{
    public ?User $user = null;

    private ?string $avatar = null;

    public function __construct(
        protected readonly UserService          $service,
        protected readonly AuthService          $authService,
        protected readonly SubscriptionService  $subscriptionService,
        protected readonly UserLocationService  $userLocationService,
        protected readonly TravelService        $travelService,
        protected readonly TravelInviteService  $inviteService,
        protected readonly TravelCommentService $travelCommentService,
        protected readonly ConversationService  $conversations,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return 'ID ' . $this->user->id . ($this->user->getFullName() ? ' | ' . $this->user->getFullName() : '');
    }

    public function view(array|Repository $httpQueryArguments = [])
    {
        $repository = is_a($httpQueryArguments, Repository::class)
            ? $httpQueryArguments
            : $this->buildQueryRepository($httpQueryArguments);

        return view($this->screenBaseView(), [
            'name'                    => $this->name(),
            'description'             => $this->description(),
            'commandBar'              => $this->buildCommandBar($repository),
            'layouts'                 => $this->build($repository),
            'formValidateMessage'     => $this->formValidateMessage(),
            'needPreventsAbandonment' => $this->needPreventsAbandonment(),
            'state'                   => $this->serializableState(),
            'controller'              => $this->frontendController(),
            'avatar'                  => $this->getAvatar(),
        ]);
    }

    public function layout(): iterable
    {
        return [];
    }

    protected function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    private function getAvatar(): ?string
    {
        return $this->avatar;
    }
}