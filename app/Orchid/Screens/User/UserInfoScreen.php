<?php

namespace App\Orchid\Screens\User;

use App\Models\UserInfo\UserInfo;
use App\Orchid\Filters\UserInfoFilter;
use App\Orchid\Layouts\User\UserInfoEditLayout;
use App\Orchid\Layouts\User\UserInfoListLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UserInfoScreen extends Screen
{
    public function name(): ?string
    {
        return 'Пользователи';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Список пользователей платформы';
    }

    public function query(): iterable
    {
        return [
            'user-info' => UserInfoFilter::runQuery(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить информацию о пользователе')
                ->modal('user_info_modal')
                ->method('saveUserInfo')
                ->modalTitle('Добавить информацию о пользователе')
                ->asyncParameters(['id' => 0])
                ->icon('plus'),
        ];
    }

    public function layout(): iterable
    {
        return [
            UserInfoFilter::displayFilterCard(),
            UserInfoListLayout::class,
            Layout::modal('user_info_modal', UserInfoEditLayout::class)->async('asyncGetUserInfo'),
        ];
    }

    public function saveUserInfo(Request $request): void
    {
        $input = $request->validate([
            'info.user_id'   => 'required|integer|unique:user_info,user_id,' . (int)$request->get('id') . ',id',
            'info.full_name' => 'required|string|max:255|min:3',
            'info.gender'    => 'required|integer|max:1|min:0',
            'info.about'     => 'nullable|string|max:8000|min:3',
            'info.birthday'  => 'nullable|date',
        ])['info'];

        $input['full_name'] = trim($input['full_name']);

        $userInfo = UserInfo::loadBy($request->get('id')) ?: new UserInfo();
        $userInfo->setUserID((int)$input['user_id']);
        $userInfo->setFullName($input['full_name']);
        $userInfo->setGender((int)$input['gender']);
        $userInfo->setAbout($input['about']);
        $userInfo->setBirthday($input['birthday']);

        $userInfo->save();

        Toast::info('Информация о пользователе успешно сохранена');
    }

    public function remove(int $id): void
    {
        $info = UserInfo::loadByOrDie($id);
        $info->delete();

        Toast::info('Информация о пользователе успешно удалена');
    }

    public function asyncGetUserInfo(int $id = 0): array
    {
        return ['info' => UserInfo::loadBy($id)];
    }

    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (UserInfoFilter::FIELDS as $item) {
            if (!is_null($request->get($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('user.info.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('user.info.list');
    }
}
