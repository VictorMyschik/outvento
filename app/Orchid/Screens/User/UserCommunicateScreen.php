<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Models\UserInfo\Communication;
use App\Orchid\Filters\UserCommunicateFilter;
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
    public string $name = 'Контакты пользователей';

    public function query(): iterable
    {
        return [
            'list' => UserCommunicateFilter::runQuery()->paginate(20),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить контакт')
                ->class('mr-btn-success')
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
            UserCommunicateFilter::displayFilterCard(),
            UserCommunicateListLayout::class,
            Layout::modal('communicate_modal', UserCommunicateEditLayout::class)->async('asyncGetCommunicate'),
        ];
    }

    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (UserCommunicateFilter::FIELDS as $item) {
            if (!is_null($request->get($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('users.communicates.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('users.communicates.list');
    }

    public function asyncGetCommunicate(int $id = 0): array
    {
        return ['communicate' => Communication::loadBy($id)];
    }

    public function saveCommunicate(Request $request): void
    {
        $input = $request->validate([
            'communicate.address' => 'required',
            'communicate.kind'    => 'required',
            'communicate.user_id' => 'required',
        ])['communicate'];

        $communicate = Communication::loadBy((int)$request->get('id')) ?: new Communication();
        $communicate->setUserId($input['user_id']);
        $communicate->setAddress($input['address']);
        $communicate->setKind((int)$input['kind']);
        $communicate->save();

        Toast::info('Контакт сохранен');
    }

    public function remove(int $id): void
    {
        $communicate = Communication::loadByOrDie($id);
        $communicate->delete();

        Toast::info('Контакт удален');
    }
}
