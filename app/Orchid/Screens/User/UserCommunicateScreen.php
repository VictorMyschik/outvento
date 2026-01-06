<?php

namespace App\Orchid\Screens\User;

use App\Models\UserInfo\Communicate;
use App\Orchid\Filters\UserInfoCommunicateFilter;
use App\Orchid\Layouts\User\UserCommunicateEditLayout;
use App\Orchid\Layouts\User\UserCommunicateListLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UserCommunicateScreen extends Screen
{
    public function name(): ?string
    {
        return 'Контакты пользователей';
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
            'user-info-address' => UserInfoCommunicateFilter::runQuery(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить контакт')
                ->modal('communicate_modal')
                ->method('saveCommunicate')
                ->modalTitle('Добавить контакт')
                ->asyncParameters(['id' => 0])
                ->icon('plus'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::modal('communicate_modal', UserCommunicateEditLayout::class)->async('asyncGetCommunicate'),
            UserInfoCommunicateFilter::displayFilterCard(),
            UserCommunicateListLayout::class,
        ];
    }

    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (UserInfoCommunicateFilter::getFilterFields() as $item) {
            if (!is_null($request->get($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('user.info.address.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('user.info.address.list');
    }

    public function asyncGetCommunicate(int $id = 0): array
    {
        return ['communicate' => Communicate::loadBy($id)];
    }

    public function saveCommunicate(Request $request): void
    {
        $input = $request->validate([
            'communicate.address' => 'required',
            'communicate.kind'    => 'required',
            'communicate.user_id' => 'required',
        ])['communicate'];

        $communicate = Communicate::loadBy((int)$request->get('id')) ?: new Communicate();
        $communicate->setUserId($input['user_id']);
        $communicate->setAddress($input['address']);
        $communicate->setKind((int)$input['kind']);
        $communicate->save();

        Toast::info('Контакт сохранен');
    }

    public function remove(int $id): void
    {
        $communicate = Communicate::loadByOrDie($id);
        $communicate->delete();

        Toast::info('Контакт удален');
    }
}
