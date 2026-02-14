<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Notifications;

use App\Models\User;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Notifications\Resolvers\CommunicationChannelSupportResolver;
use App\Services\Notifications\Resolvers\NotificationAudienceResolver;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class UserNotificationSettingEditLayout extends Listener
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    protected $targets = [
        'setting.user_id',
        'setting.communication_id',
        'setting.event',
    ];

    protected function layouts(): iterable
    {
        $communications = $this->userService->getCommunications((int)$this->query->get('setting.user_id'));

        $options = [];
        foreach ($communications as $communication) {
            $channel = CommunicationChannelSupportResolver::fromCommunicationType($communication->getType());
            if ($channel) {
                $options[$communication->id] = $channel->getLabel() . ': ' . $communication->address;
            }
        }

        $eventTypeOptions = ServiceEvent::getSelectList();
        if ($user = User::find((int)$this->query->get('setting.user_id'))) {
            $eventTypeOptions = ServiceEvent::selectListForAudiences(
                NotificationAudienceResolver::fromUser($user)
            );
        }

        return [
            Layout::rows([
                    Relation::make('setting.user_id')
                        ->fromModel(User::class, 'name', 'id')
                        ->required()
                        ->title('Пользователь'),


                    Select::make('setting.event')
                        ->options($eventTypeOptions)
                        ->required()
                        ->title('Тип события'),


                    Select::make('setting.communication_id')
                        ->options($options)
                        ->title('Адрес для уведомлений')
                        ->required(),
                ]
            ),
        ];
    }

    public function handle(Repository $repository, Request $request): Repository
    {
        return $repository->set('setting.user_id', $request->input('setting.user_id'))
            ->set('setting.event', $request->input('setting.event'))
            ->set('setting.communication_id', $request->input('setting.communication_id'));
    }
}
