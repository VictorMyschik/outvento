<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Http\Controllers\API\User\Request\UpdateProfileRequest;
use App\Models\User;
use App\Orchid\Filters\User\UserInfoFilter;
use App\Orchid\Layouts\User\UserInfoListLayout;
use App\Orchid\Layouts\User\UserProfileEditLayout;
use App\Services\User\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UserListInfoScreen extends Screen
{
    public function __construct(
        private readonly Request     $request,
        private readonly UserService $service,
    ) {}

    public string $name = 'Пользователи';

    public function query(): iterable
    {
        return [
            'list' => UserInfoFilter::runQuery()->paginate(50),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить информацию о пользователе')
                ->class('mr-btn-success')
                ->modal('user_info_modal')
                ->method('saveUserInfo')
                ->modalTitle('Добавить информацию о пользователе')
                ->asyncParameters(['id' => 0])
                ->novalidate()
                ->icon('plus'),
        ];
    }

    public function layout(): iterable
    {
        return [
            // UserInfoFilter::displayFilterCard($this->request),
            UserInfoListLayout::class,
            Layout::modal('user_modal', UserProfileEditLayout::class)->async('asyncGetUserProfile'),
        ];
    }

    public function asyncGetUserProfile(int $id): array
    {
        return User::find($id)->getAttributes();
    }

    public function saveUser(Request $request, int $id): void
    {
        $input = $request->validate(new UpdateProfileRequest()->rules($id), $request->all());

        $input['email_verified_at'] = $request->get('email_verified_at') ? now() : null;
        $input['telegram_chat_id'] = $request->get('telegram_chat_id') ?? null;
        unset($input['telegram']);

        $this->service->updateUser(User::find($id), $input);

        Toast::info('Информация о пользователе успешно сохранена');
    }

    public function asyncGetUserInfo(int $id = 0): array
    {
        return ['info' => User::loadBy($id)];
    }

    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (UserInfoFilter::FIELDS as $item) {
            if (!is_null($request->get($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('users.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('users.list');
    }
}
