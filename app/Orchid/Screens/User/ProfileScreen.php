<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Http\Controllers\API\User\Request\CommunicationRequest;
use App\Http\Controllers\API\User\Request\UpdateProfileRequest;
use App\Http\Controllers\API\User\Request\UserLocationRequest;
use App\Models\TravelInvite;
use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Orchid\Layouts\User\Profile\AvatarUploadLayout;
use App\Orchid\Layouts\User\Profile\UserLanguagesEditLayout;
use App\Orchid\Layouts\User\Profile\UserLocationLayout;
use App\Orchid\Layouts\User\Profile\UserNotificationSettingsEditLayout;
use App\Orchid\Layouts\User\TelegramDeeplinkLayout;
use App\Orchid\Layouts\User\UserBaseScreen;
use App\Orchid\Layouts\User\UserCommunicateEditLayout;
use App\Orchid\Layouts\User\UserProfileEditLayout;
use App\Orchid\Layouts\User\UserRolesEditLayout;
use App\Orchid\Rebuild\MrTabs;
use App\Services\Notifications\DTO\ServiceNotificationDto;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\PromoEvent;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Notifications\Resolvers\NotificationAudienceResolver;
use App\Services\Promo\DTO\SubscriptionDto;
use App\Services\Promo\Enum\PromoSource;
use App\Services\Promo\Enum\Status;
use App\Services\System\Enum\Language;
use App\Services\User\Enum\CommunicationType;
use App\Services\User\Enum\VerificationStatus;
use App\Services\User\Enum\Visibility;
use App\Services\User\Google\DTO\UserLocationDto;
use Illuminate\Http\Request;
use Orchid\Platform\Models\Role;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Layouts\Tabs;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ProfileScreen extends UserBaseScreen
{
    public ?User $user = null;

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
        $this->setAvatar($user->getAvatar());
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
        $out[] = Layout::rows($this->getActionTopLayout());

        $out[] = Layout::columns([
            $this->getLeftLayout(),
            $this->getRightLayout(),
        ]);
        $out[] = Layout::rows($this->getActionBottomLayout());

        $out[] = Layout::modal('communicate_modal', UserCommunicateEditLayout::class)->async('asyncGetCommunicate');
        $out[] = Layout::modal('upload_user_photo', AvatarUploadLayout::class);
        $out[] = Layout::modal('user_modal', UserProfileEditLayout::class)->async('asyncGetUserProfile');
        $out[] = Layout::modal('user_notification_settings_modal', UserNotificationSettingsEditLayout::class)->async('asyncGetServiceUserNotificationSettings')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('user_role_modal', UserRolesEditLayout::class)->async('asyncGetUserRoles');
        $out[] = Layout::modal('telegram_deep_link_modal', TelegramDeeplinkLayout::class)->async('asyncGetTelegramDeepLink')->size(Modal::SIZE_LG)->withoutApplyButton();
        $out[] = Layout::modal('user_location', UserLocationLayout::class);
        $out[] = Layout::modal('user_languages', UserLanguagesEditLayout::class)->async('asyncGetUserLanguages');

        return $out;
    }

    private function getActionTopLayout(): array
    {
        return [
            Link::make('Travels')->class('mr-btn mr-btn-route')->icon('map')->route('profiles.travels', $this->user->id),
        ];
    }

    public function asyncGetUserLanguages(): array
    {
        $list = $this->service->getUserLanguages($this->user, Language::from($this->user->language));

        return [
            'languages' => array_flip($list),
        ];
    }

    public function asyncGetServiceUserNotificationSettings(int $event): array
    {
        return [
            'active'                => $this->service->isUserNotificationActive($this->user->id, ServiceEvent::from($event)),
            'notificationEventType' => $event,
            'userSettings'          => $this->service->getServiceUserNotificationList($this->user->id),
            'userCommunications'    => $this->service->getCommunicationsForNotification($this->user->id),
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

        $this->service->updateUserRoles($this->user, $input);

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
                ->method('sendVerifyUserEmail', ['id' => $this->user->id]);
        }

        $locationData = [
            'location'  => [
                'city'    => $this->user->getUserLocation()?->getCity()->getName(Language::RU),
                'country' => $this->user->getUserLocation()?->getCity()->getCountry()->name_ru,
                'btns'    => Group::make([
                    ModalToggle::make('')
                        ->icon('pencil')
                        ->class('mr-btn-primary')
                        ->modal('user_location')
                        ->modalTitle('Set location')
                        ->method('saveUserLocation'),
                    Button::make('')
                        ->icon('trash')
                        ->class('mr-btn-danger')
                        ->confirm('Are you sure you want to delete user location?')
                        ->method('deleteUserLocation'),
                ])->autoWidth(),
            ],
            'languages' => [
                'list' => $this->service->getUserLanguages($this->user, Language::from($this->user->language)),
                'btns' => Group::make([
                    ModalToggle::make('')
                        ->icon('pencil')
                        ->class('mr-btn-primary')
                        ->modal('user_languages')
                        ->modalTitle('Set languages')
                        ->method('saveUserLanguages'),
                    Button::make('')
                        ->icon('trash')
                        ->class('mr-btn-danger')
                        ->confirm('Are you sure you want to delete user languages?')
                        ->method('deleteUserLanguages'),
                ])->autoWidth(),
            ],
        ];

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
                Label::make('gender')->title('Gender')->value($this->user->getGender()?->getLabel() ?: '-'),
                Label::make('birthday')->title('Birthday')->value(
                    $this->user->birthday ? $this->user->birthday->format('d.m.Y') : '-'
                ),
                Label::make('language')->title('Default Language')->value($this->user->getLanguage()->getLabel() ?? '-'),
            ]),
            ViewField::make('')->view('hr'),
            ViewField::make('')->view('admin.users.location_language')->value($locationData),
            ViewField::make('')->view('hr'),
            Group::make([
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

    public function saveUserLanguages(Request $request): void
    {
        $input = $request->get('languages', []);

        $this->service->updateUserLanguages($this->user, $input);
    }

    public function deleteUserLanguages(): void
    {
        $this->service->deleteUserLanguages($this->user);
    }

    private function getRightLayout(): Tabs
    {
        return MrTabs::make([
            'Аватар'               => Layout::rows($this->avatarTab()),
            'Communication'        => Layout::rows($this->getCommunicationLayout()),
            'Service Notification' => Layout::rows($this->getServiceNotificationLayout()),
            'Promo Notification'   => Layout::rows($this->getPromoNotificationLayout()),
            'Travel Invites'       => Layout::rows($this->getTravelInvitesLayout()),
        ]);
    }

    public function getTravelInvitesLayout(): array
    {
        $rows['header'] = ['Title', 'Date', 'City', '#'];

        $list = $this->inviteService->getListByUser($this->user->id);

        /** @var TravelInvite $item */
        foreach ($list as $item) {
            $travel = $item->getTravel();

            $rowBtns = [
                Link::make('Open travel')
                    ->icon('map')
                    ->href(route('profiles.travel.details', ['user' => $travel->getOwnerId(), 'travel' => $travel->id]))
                    ->target('_blank')
            ];

            $rows['body'][] = [
                'Title' => $travel->getTitle(),
                'Date'  => $travel->date_from?->format('d.m.Y') ?? '-',
                'City'  => $travel->start_city_id ?: '-',
                '#'     => DropDown::make()->icon('bs.three-dots-vertical')->list($rowBtns),
            ];
        }

        return [
            ViewField::make('')->view('admin.table')->value($rows),
        ];
    }

    private function getPromoNotificationLayout(): array
    {
        $btns = [];
        $rows = [];
        $rows['header'] = ['Event', 'Status', 'Created', '#'];

        foreach (PromoEvent::cases() as $event) {
            $rowBtns = [];
            $subscription = $this->subscriptionService->getUserSubscriptionByEvent($this->user, $event);

            if ($subscription) {
                if ($subscription->getStatus() === Status::Revoked) {
                    $rowBtns[] = Button::make('add again')
                        ->icon('plus')
                        ->confirm('Will add the user subscription to the notifications again. Are you sure?')
                        ->method('addSubscription', ['event' => $event->value]);
                }

                if ($subscription->getStatus() === Status::Pending) {
                    $rowBtns[] = Button::make('confirm')
                        ->icon('check')
                        ->confirm('Are you sure you want to confirm the subscription?')
                        ->method('confirmSubscription', ['id' => $subscription->id()]);
                }

                if ($subscription->getStatus() === Status::Confirmed) {
                    $rowBtns[] = Button::make('revoke')
                        ->icon('xmark')
                        ->confirm('Will unsubscribe the user from the notifications, but keep the subscription history. Are you sure?')
                        ->method('revokeSubscription', ['token' => $subscription?->getUnsubscribeToken()]);
                }

                $rowBtns[] = Button::make('remove')
                    ->icon('trash')
                    ->confirm('Are you sure you want to delete the subscription permanently?')
                    ->method('deleteSubscription', ['token' => $subscription?->getUnsubscribeToken()]);
            } else {
                $rowBtns[] = Button::make('add')
                    ->icon('plus')
                    ->method('addSubscription', ['event' => $event->value]);
            }

            $rows['body'][] = [
                'Event'   => $event->getLabel(),
                'Status'  => $subscription?->getStatus()->getLabel() ?: '-',
                'Created' => $subscription?->optin_at?->format('H:i d/m/Y T') ?? '-',
                '#'       => DropDown::make()->icon('bs.three-dots-vertical')->list($rowBtns),
            ];
        }

        return [
            Group::make($btns)->autoWidth(),
            ViewField::make('')->view('hr'),
            ViewField::make('')->view('admin.table')->value($rows),
        ];
    }

    public function confirmSubscription(int $id): void
    {
        $this->subscriptionService->confirmSubscription($this->subscriptionService->getSubscriptionById($id));
    }

    public function revokeSubscription(string $token): void
    {
        $this->subscriptionService->revokeSubscription($token);
    }

    public function deleteSubscription(string $token): void
    {
        $this->subscriptionService->deleteSubscription($token);
    }

    public function addSubscription(string $event): void
    {
        $this->subscriptionService->createSubscriptionWithNotify(new SubscriptionDto(
            email: $this->user->email,
            language: Language::from($this->user->language),
            event: PromoEvent::from($event),
            source: PromoSource::Admin,
        ));

        Toast::info('User subscribed to promo notifications successfully');
    }

    private function getCommunicationLayout(): array
    {
        $communications = $this->service->getCommunications($this->user->id);

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

        /** @var Communication $communication */
        foreach ($communications as $communication) {
            $rowBtns = [];

            if ($communication->getType() === CommunicationType::Email) {
                if ($communication->getVerificationStatus()->isVerified()) {
                    $rowBtns[] = Button::make('Отозвать верификацию')
                        ->icon('xmark')
                        ->confirm('Are you sure you want to revoke the verification?')
                        ->method('revokeVerifyCommunicationEmail', ['communicationId' => $communication->id]);
                } else {
                    $rowBtns[] = Button::make('Отправить подтверждение')
                        ->icon('envelope')
                        ->confirm('Are you sure you want to send a verification email to the user?')
                        ->method('sendVerifyCommunicationEmail', ['communicationId' => $communication->id]);
                    $rowBtns[] = Button::make('Подтвердить вручную')
                        ->icon('check-circle')
                        ->method('confirmVerifyCommunicationEmail', ['communicationId' => $communication->id]);
                }
            }

            if ($communication->getType() === CommunicationType::Telegram) {
                $rowBtns[] = Link::make('Open in Telegram')
                    ->icon('telegram')
                    ->href($communication->getTelegramLink())
                    ->target('_blank');
                $rowBtns[] = ModalToggle::make('Add boot by deeplink')
                    ->icon('link')
                    ->modalTitle('Open Telegram Deep Link')
                    ->modal('telegram_deep_link_modal')
                    ->asyncParameters(['communicationId' => $communication->id]);
            }

            $rowBtns[] = ModalToggle::make('изменить')
                ->icon('pencil')
                ->modal('communicate_modal')
                ->modalTitle('Add communication')
                ->method('saveCommunication', ['id' => $communication->id]);

            $rowBtns[] = Button::make('удалить')
                ->icon('trash')
                ->confirm('Are you sure you want to delete the communication?')
                ->method('deleteCommunication', ['id' => $communication->id]);

            $rows['body'][] = [
                'Type'        => $communication->getType()->getLabel(),
                'Visibility'  => Visibility::from($communication->visibility)->getLabel(),
                'Address'     => $this->getAddressDisplay($communication),
                'Description' => $communication->description ?: '-',
                'Created'     => $communication->created_at,
                '#'           => DropDown::make()->icon('bs.three-dots-vertical')->list($rowBtns),
            ];
        }

        return [
            Group::make($btns)->autoWidth(),
            ViewField::make('')->view('hr'),
            ViewField::make('')->view('admin.table')->value($rows),
        ];
    }

    public function asyncGetTelegramDeepLink(int $communicationId): array
    {
        return [
            'link' => Communication::loadByOrDie($communicationId)->getTelegramDeepLink()
        ];
    }

    private function getAddressDisplay(Communication $communication): string
    {
        if ($communication->getType() === CommunicationType::Email || $communication->getType() === CommunicationType::Telegram) {
            if (VerificationStatus::from($communication->verification_status)->isVerified()) {
                return $communication->address . ' <i class="fa fa-check" style="color: green" title="Verified"></i>';
            } else {
                return $communication->address . ' <i class="fa fa-times" style="color: red" title="Not verified"></i>';
            }
        } else {
            return $communication->address;
        }
    }

    public function getServiceNotificationLayout(): array
    {
        $serviceNotifications = $this->service->getServiceUserNotificationList($this->user->id);

        $header = ['Active', 'Event Type'];

        $header = array_merge($header, NotificationChannel::getSelectList(), ['#']);

        foreach (ServiceEvent::selectListForAudiences(NotificationAudienceResolver::fromUser($this->user)) as $key => $serviceNotificationType) {
            $row = [
                'Active'     => $this->service->isUserNotificationActive($this->user->id, ServiceEvent::from($key)) ? '<i class="fa fa-check" style="color: green"></i>' : '<i class="fa fa-times" style="color: red"></i>',
                'Event Type' => $serviceNotificationType,
            ];
            $row = array_merge($row, array_fill_keys(NotificationChannel::getSelectList(), null));

            foreach ($serviceNotifications as $userSettings) {
                if ($userSettings->event === $key) {
                    $channel = $userSettings->getChannel();

                    $verificationStatus = ' <i class="fa fa-times" style="color: red" title="Not verified"></i>';
                    if ($userSettings->getCommunication()->getVerificationStatus()->isVerified()) {
                        $verificationStatus = ' <i class="fa fa-check" style="color: green" title="Verified"></i>';
                    }

                    $row[$channel->getLabel()] = $userSettings->getCommunication()->address . $verificationStatus;
                }
            }

            $row['#'] = ModalToggle::make('')
                ->class('mr-btn-primary fa fa-pencil')
                ->modal('user_notification_settings_modal')
                ->modalTitle('Edit Notification Settings')
                ->method('saveServiceUserNotifications', ['event' => $key]);

            $rows['body'][] = $row;
        }

        $rows['header'] = $header;

        return [
            Group::make([
                ViewField::make('Subscription token')->view('admin.tab_title')->value([
                    'title'       => 'Users service notifications',
                    'description' => $this->user->getRolesDisplay()
                ]),
                Button::make('удалить все')
                    ->class('mr-btn-danger pull-right')
                    ->confirm('Are you sure you want to delete the notifications?')
                    ->method('deleteAllNotifications'),
            ])->alignCenter(),
            ViewField::make('')->view('hr'),
            ViewField::make('')->view('admin.table')->value($rows),
            ViewField::make('')->view('hr'),

            ViewField::make('Subscription token')->view('admin.h5')->value('Global users subscription token'),
            Label::make('Subscription token')
                ->help('Токен, используемый для отображения всех настроек оповещения на странице пользователя'),

            Group::make([
                Label::make('Subscription token')->value($this->user->subscription_token),
                Button::make('')->class('mr-btn-primary')->icon('refresh')->method('generateSubscriptionToken'),
            ])->autoWidth(),
        ];
    }

    public function confirmVerifyCommunicationEmail(int $communicationId): void
    {
        $this->service->saveCommunicationManually($communicationId, ['verification_status' => VerificationStatus::Verified->value]);
    }

    public function revokeVerifyCommunicationEmail(int $communicationId): void
    {
        $this->service->saveCommunicationManually($communicationId, ['verification_status' => VerificationStatus::NotVerified->value]);
    }

    public function generateSubscriptionToken(): void
    {
        $this->service->updateUser($this->user, ['subscription_token' => $this->authService->generateSubscriptionToken()]);
    }

    public function deleteAllNotifications(): void
    {
        $this->service->resetToDefaultUserNotifications($this->user->id);
    }

    public function saveCommunication(CommunicationRequest $request, int $id): void
    {
        $input = $request->getUpdateData();
        $input['user_id'] = $this->user->id;
        $input['verification_status'] = $request->get('verification_status', 0);

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

        if ($input['birthday']) {
            $input['birthday'] = date('Y-m-d', strtotime($input['birthday']));
        }

        $this->service->updateUser(User::find($id), $input);

        Toast::info('Информация о пользователе успешно сохранена');
    }

    public function saveServiceUserNotifications(Request $request, int $event): void
    {
        $input = $request->all();
        $event = ServiceEvent::from($event);
        $active = (bool)($input['active'] ?? false);

        $this->service->updateNotificationMute($this->user->id, $event, $active);

        foreach ($input as $key => $communicationId) {
            if (!str_starts_with($key, 'channel_')) {
                continue;
            }

            $this->service->updateUserServiceNotification(
                userId: $this->user->id,
                event: $event,
                dto: new ServiceNotificationDto(
                    userId: $this->user->id,
                    event: $event,
                    channel: NotificationChannel::from(str_replace('channel_', '', $key)),
                    communicationId: (int)$communicationId,
                ));
        }

        Toast::info('Сервисные уведомления успешно сохранены')->delay(1500);
    }

    public function saveUserLocation(UserLocationRequest $request): void
    {
        $this->userLocationService->saveUserLocation(
            userId: $this->user->id,
            dto: new UserLocationDto(
                placeId: $request->getPlaceId(),
                lat: $request->getLat(),
                lng: $request->getLng(),
                countryCode: $request->getCountryCode(),
                cityName: $request->getCityName(),
                language: Language::fromCode(app()->getLocale()),
            ),
        );
    }

    public function deleteUserLocation(): void
    {
        $this->userLocationService->deleteUserLocation($this->user->id);
    }

    public function sendVerifyUserEmail(): void
    {
        $this->authService->sendVerifyNotification($this->user);
    }

    public function sendVerifyCommunicationEmail(int $communicationId): void
    {
        $this->service->sendCommunicationVerifyEmail(Communication::loadByOrDie($communicationId));
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
