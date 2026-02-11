<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Http\Controllers\API\User\Request\CommunicationRequest;
use App\Http\Controllers\API\User\Request\UpdateProfileRequest;
use App\Models\Notification\NotificationEventType;
use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Orchid\Layouts\User\Profile\AvatarUploadLayout;
use App\Orchid\Layouts\User\Profile\UserNotificationSettingsEditLayout;
use App\Orchid\Layouts\User\UserCommunicateEditLayout;
use App\Orchid\Layouts\User\UserProfileEditLayout;
use App\Orchid\Layouts\User\UserRolesEditLayout;
use App\Orchid\Rebuild\MrTabs;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\System\Enum\Language;
use App\Services\User\Enum\Visibility;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Orchid\Platform\Models\Role;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Layouts\Tabs;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ProfileScreen extends Screen
{
    public ?User $user = null;

    public function __construct(
        private readonly UserService $service,
    ) {}

    public function name(): string
    {
        return 'ID ' . $this->user->id . ($this->user->getFullName() ? ' | ' . $this->user->getFullName() : '');
    }

    public function description(): string
    {
        return $this->user->getRolesDisplay() . ' | ' . View('admin.created_updated', ['value' => $this->user])->toHtml();
    }

    public function query(User $user): iterable
    {
        return [
            'user' => $user,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Назад')->class('mr-btn mr-btn-route')->icon('arrow-up')->route('profiles.list'),
        ];
    }

    public function layout(): iterable
    {
        $out[] = Layout::columns([
            $this->getLeftLayout(),
            $this->getRightLayout(),
        ]);
        $out[] = Layout::rows($this->getActionBottomLayout());

        $out[] = Layout::modal('communicate_modal', UserCommunicateEditLayout::class)->async('asyncGetCommunicate');
        $out[] = Layout::modal('upload_user_photo', AvatarUploadLayout::class);
        $out[] = Layout::modal('user_modal', UserProfileEditLayout::class)->async('asyncGetUserProfile');
        $out[] = Layout::modal('user_notification_settings_modal', UserNotificationSettingsEditLayout::class)->async('asyncGetUserNotificationSettings')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('user_role_modal', UserRolesEditLayout::class)->async('asyncGetUserRoles');

        return $out;
    }

    public function asyncGetUserNotificationSettings(int $eventTypeId = 0): array
    {
        if ($eventTypeId) {
            $notificationEventTypes[] = NotificationEventType::loadByOrDie($eventTypeId);
        }

        return [
            'notificationEventTypes' => $notificationEventTypes ?: $this->service->getNotificationTypesForUser($this->user),
            'userSettings'           => $this->service->getUserNotificationSettingsList($this->user->id, Language::RU),
            'userCommunications'     => $this->service->getCommunicationsForNotification($this->user->id),
        ];
    }

    public function asyncGetUserProfile(int $id): array
    {
        return User::find($id)->getAttributes();
    }

    public function asyncGetUserRoles(int $id): array
    {
        return [
            'roleOptions' => Role::pluck('name', 'id')->toArray(),
            'roles'       => $this->user->getRoles()->pluck('name', 'id')->toArray()
        ];
    }

    public function asyncGetCommunicate(int $id = 0): array
    {
        return array_merge(['user_id' => $this->user->id], Communication::loadBy($id)?->getAttributes() ?: []);
    }

    private function getActionBottomLayout(): array
    {
        return [
            Group::make([
                ModalToggle::make('User Roles')
                    ->icon('shield')
                    ->class('mr-btn-primary pull-left')
                    ->modal('user_role_modal')
                    ->modalTitle('User Roles for ' . $this->user->name)
                    ->method('saveUserRoles')
                    ->asyncParameters(['id' => $this->user->id]),
                Button::make('Удалить пользователя')
                    ->class('mr-btn-danger pull-right')
                    ->icon('trash')
                    ->method('removeUser')
                    ->confirm('Вы уверены, что хотите удалить этого пользователя?'),
            ])->alignCenter()
        ];
    }

    public function saveUserRoles(Request $request): void
    {
        $input = $request->get('roles', []);

        $this->service->updateUserRoles($this->user->id, $input);

        Toast::message('User roles saved successfully');
    }

    public function removeUser(): void
    {
        $this->service->deleteUser($this->user);
    }

    private function getLeftLayout(): Rows
    {
        if ($this->user->email_verified_at) {
            $verifiedInput = Label::make('email_verified_at')
                ->title('Email verified')
                ->value($this->user->email_verified_at->format('H:i d/m/Y T'));
        } else {
            $verifiedInput = Button::make('Отправить код верификации')
                ->class('mr-btn-primary')
                ->title('Email verification')
                ->hidden((bool)$this->user->email_verified_at)
                ->confirm('Are you sure you want to send a verification email to the user?')
                ->method('sendVerifyEmail', ['id' => $this->user->id]);
        }

        return Layout::rows([
            Group::make([
                Label::make('id')->title('ID')->value((string)$this->user->id),
                Label::make('name')->title('Auth Login')->value($this->user->name),
                Label::make('email')->title('Auth Email')->value($this->user->email),
                $verifiedInput
            ]),
            ViewField::make('')->view('hr'),
            Group::make([
                Label::make('first_name')->title('First name')->value($this->user->first_name ?: '-'),
                Label::make('last_name')->title('Last name')->value($this->user->last_name ?: '-'),
                Label::make('language')->title('Language')->value($this->user->getLanguage()->getLabel() ?? '-')
            ]),

            ViewField::make('')->view('hr'),
            Group::make([
                Label::make('birthday')->title('Дата рождения')->value(
                    $this->user->birthday ? $this->user->birthday->format('d.m.Y') : '-'
                ),
                Label::make('gender')->title('Пол')->value($this->user->getGender()?->getLabel() ?: '-'),
                Label::make('relationship_status')->title('Семейное положение')->value($this->user->getRelationshipStatus()->getLabel()),

            ]),
            ViewField::make('')->view('hr'),
            Label::make('about')->title('О себе')->value($this->user->about ?: '-'),
            ModalToggle::make('изменить')
                ->class('mr-btn-primary pull-right')
                ->modal('user_modal')
                ->modalTitle('User id ' . $this->user->id)
                ->method('saveUser')
                ->asyncParameters(['id' => $this->user->id]),
        ]);
    }

    private function getRightLayout(): Tabs
    {
        return MrTabs::make([
            'Аватар'        => Layout::rows($this->avatarTab()),
            'Communication' => Layout::rows($this->getCommunicationLayout()),
            'Notification'  => Layout::rows($this->getNotificationLayout()),
        ]);
    }

    private function getCommunicationLayout(): array
    {
        $communications = $this->service->getCommunications($this->user->id, Language::RU);

        $btns[] = ModalToggle::make('добавить')
            ->class('mr-btn-success')
            ->modal('communicate_modal')
            ->modalTitle('Add communication')
            ->method('saveCommunication', ['id' => 0]);

        if (!empty($communications)) {
            $btns[] = Button::make('удалить все')
                ->class('mr-btn-danger')
                ->confirm('Are you sure you want to delete the communications?')
                ->method('deleteAllCommunications');
        }

        $rows['header'] = ['Type', 'Visibility', 'Address', 'Description', 'Created', '#'];

        foreach ($communications as $communication) {
            $rows['body'][] = [
                'Type'        => $communication->communication_type,
                'Visibility'  => Visibility::from($communication->visibility)->getLabel(),
                'Address'     => $communication->address,
                'Description' => $communication->description ?: '-',
                'Created'     => $communication->created_at,
                '#'           => Group::make([
                    ModalToggle::make('')
                        ->class('mr-btn-primary')
                        ->icon('pencil')
                        ->modal('communicate_modal')
                        ->modalTitle('Add communication')
                        ->method('saveCommunication', ['id' => $communication->id]),
                    Button::make('')
                        ->class('mr-btn-danger')
                        ->icon('trash')
                        ->confirm('Are you sure you want to delete the communication?')
                        ->method('deleteCommunication', ['id' => $communication->id]),
                ])->autoWidth(),
            ];
        }

        return [
            Group::make($btns)->autoWidth(),
            ViewField::make('')->view('hr'),
            ViewField::make('')->view('admin.table')->value($rows),
        ];
    }

    public function getNotificationLayout(): array
    {
        $userNotificationSettingsList = $this->service->getUserNotificationSettingsList($this->user->id, Language::RU);
        $communicationTypes = $this->service->getCommunicationChannelTypes($this->user->id);

        $header = ['Active', 'Event Type'];
        $channels = [];
        foreach ($communicationTypes as $communicationType) {
            $channel = NotificationChannel::tryFrom($communicationType->code);
            if ($channel) {
                $channels[$communicationType->id] = $channel->getLabel();
            }
        }

        $header = array_merge($header, $channels, ['Updated', '#']);

        /** @var NotificationEventType $notificationEventType */
        foreach ($this->service->getNotificationTypesForUser($this->user) as $notificationEventType) {
            $row = [
                'Event Type' => $notificationEventType->getTitle(),
                'Active'     => null,
                'Updated'    => null,
            ];
            $row = array_merge($row, array_fill_keys($channels, null));

            foreach ($userNotificationSettingsList as $userSettings) {
                if ($userSettings->event_type_id === $notificationEventType->id) {
                    $row['Active'] = $userSettings->active ? "<i class='fa fa-check text-success' aria-hidden='true'></i>" : "<i class='fa fa-times text-danger' aria-hidden='true'></i>";;
                    $row['Updated'] = $userSettings->updated_at?->format('H:i d/m/Y');

                    $channel = NotificationChannel::tryFrom($userSettings->getCommunication()->getType()->code);

                    $row[$channel->getLabel()] = $userSettings->getCommunication()->address ?? '-';
                }
            }
            $row['#'] = ModalToggle::make('')
                ->class('mr-btn-primary fa fa-pencil')
                ->modal('user_notification_settings_modal')
                ->modalTitle('Edit Notification Settings')
                ->method('saveUserCommunicationSettings', ['eventTypeId' => $notificationEventType->id]);

            $rows['body'][] = $row;
        }

        $rows['header'] = $header;

        return [
            Group::make([
                ModalToggle::make('изменить')
                    ->class('mr-btn-primary')
                    ->modal('user_notification_settings_modal')
                    ->modalTitle('Edit Notification Settings')
                    ->method('saveUserCommunicationSettings'),
                Button::make('удалить все')
                    ->class('mr-btn-danger pull-right')
                    ->confirm('Are you sure you want to delete the notifications?')
                    ->method('deleteAllNotifications'),
            ])->alignCenter(),
            ViewField::make('')->view('hr'),
            ViewField::make('')->view('admin.table')->value($rows),
        ];
    }

    public function deleteAllNotifications(): void
    {
        $this->service->resetToDefaultUserNotifications($this->user->id);
    }

    public function saveCommunication(CommunicationRequest $request, int $id): void
    {
        $input = $request->getUpdateData();
        $input['user_id'] = $this->user->id;

        $this->service->saveCommunication($id, $input);

        Toast::info('Communication saved successfully');
    }

    private function avatarTab(): array
    {
        $hasLogo = (bool)$this->user->getAvatar();

        $photoTab = [
            Group::make([
                ModalToggle::make('Загрузить аватар')
                    ->class('mr-btn-success')
                    ->modal('upload_user_photo')
                    ->modalTitle('Загрузить аватар')
                    ->method('saveUserAvatar', ['userId' => $this->user->id]),
                Button::make('Удалить изображение')
                    ->class('mr-btn-danger')
                    ->method('removeLogo')
                    ->hidden(!$hasLogo)
                    ->confirm('Вы уверены, что хотите удалить изображение?')
                    ->parameters(['userId' => $this->user->id]),
            ])->autoWidth(),
        ];

        $group[] = ViewField::make('#')->view('admin.users.avatar')->value(['path' => $this->user->getAvatar()]);

        return array_merge($photoTab, [ViewField::make('')->view('space')], $group);
    }

    public function deleteAllCommunications(): void
    {
        $this->service->deleteAllCommunication($this->user->id);
    }

    public function deleteCommunication(int $id): void
    {
        $this->service->deleteCommunication($this->user->id, $id);
    }

    public function saveUser(UpdateProfileRequest $request, int $id): void
    {
        $input = $request->getUpdateData();

        $input['email_verified_at'] = $request->get('email_verified_at') ? now() : null;
        $input['subscription_token'] = $request->get('subscription_token') ?? null;
        unset($input['telegram']);

        if ($input['birthday']) {
            $input['birthday'] = date('Y-m-d', strtotime($input['birthday']));
        }

        $this->service->updateUser(User::find($id), $input);

        Toast::info('Информация о пользователе успешно сохранена');
    }

    public function saveUserCommunicationSettings(Request $request, int $eventTypeId = 0): void
    {
        $input = $request->all();
        $allowed = $eventTypeId ? [NotificationEventType::loadByOrDie($eventTypeId)] : $this->service->getNotificationTypesForUser($this->user);
        $data = [];
        foreach ($input as $key => $value) {
            if (!$value) {
                continue;
            }

            if (str_contains($key, '|')) {
                [$eventTypeCode, $channelCode] = explode('|', $key);

                foreach ($allowed as $allowedType) {
                    foreach (NotificationChannel::cases() as $channel) {
                        if ($allowedType->code === $eventTypeCode && $channel->value === $channelCode) {
                            $data[$allowedType->id][$channelCode] = [
                                'active'           => true,
                                'user_id'          => $this->user->id,
                                'event_type_id'    => $allowedType->id,
                                'communication_id' => (int)$value,
                            ];

                            break 2;
                        }
                    }
                }
            }
        }

        $this->service->updateUserNotificationSetting($this->user->id, $eventTypeId, $data);

        Toast::info('Настройки уведомлений успешно сохранены')->delay(1500);
    }

    public function saveUserAvatar(Request $request): void
    {
        $file = $request->file('avatar');

        $this->service->addAvatar($this->user->id, $file);
    }

    public function removeLogo(): void
    {
        $this->service->removeAvatar($this->user);
    }
}
