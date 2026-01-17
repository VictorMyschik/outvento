<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Notifications;

use App\Models\User;
use App\Services\Notifications\Enum\EventType;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\System\Enum\Language;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\ViewField;
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
        'setting.event_type',
        'setting.active',
    ];

    protected function layouts(): iterable
    {
        $communications = $this->userService->getCommunications((int)$this->query->get('setting.user_id'), Language::EN);
        $options = [];

        foreach ($communications as $communication) {
            if (isset(NotificationChannel::getSelectList()[$communication->code])) {
                $options[$communication->id] = $communication->communication_type . ' - ' . $communication->address;
            }
        }

        return [
            Layout::rows([
                    Group::make([
                        Switcher::make('setting.active')->sendTrueOrFalse()->title('Active'),

                        Relation::make('setting.user_id')
                            ->fromModel(User::class, 'name', 'id')
                            ->required()
                            ->title('Пользователь'),
                    ]),

                    ViewField::make('')->view('space'),

                    Select::make('setting.event_type')
                        ->options(EventType::getSelectList())
                        ->required()
                        ->title('Тип оповещения'),

                    Select::make('setting.communication_id')
                        ->options($options)
                        ->title('Способ оповещения')
                        ->required(),
                ]
            ),
        ];
    }

    public function handle(Repository $repository, Request $request): Repository
    {
        return $repository->set('setting.user_id', $request->input('setting.user_id'))
            ->set('setting.event_type', $request->input('setting.event_type'))
            ->set('setting.communication_id', $request->input('setting.communication_id'))
            ->set('setting.active', $request->input('setting.active'));
    }
}
