<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Http\Controllers\API\Auth\Request\Auth\RegisterRequest;
use App\Http\Controllers\API\User\Request\UpdateProfileRequest;
use App\Models\User;
use App\Orchid\Filters\User\UserInfoFilter;
use App\Orchid\Layouts\User\NewUserLayout;
use App\Orchid\Layouts\User\UserInfoListLayout;
use App\Orchid\Layouts\User\UserProfileEditLayout;
use App\Services\System\Enum\Language;
use App\Services\User\DTO\UserProfileDTO;
use App\Services\User\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UserProfileListScreen extends Screen
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
            ModalToggle::make('Создать пользователя')
                ->class('mr-btn-success')
                ->modal('create_user_modal')
                ->method('createNewUser')
                ->modalTitle('Добавить нового пользователя')
                ->novalidate()
                ->icon('plus'),
        ];
    }

    public function layout(): iterable
    {
        return [
            UserInfoFilter::displayFilterCard($this->request),
            UserInfoListLayout::class,
            Layout::modal('user_modal', UserProfileEditLayout::class)->async('asyncGetUserProfile'),
            Layout::modal('create_user_modal', NewUserLayout::class),
        ];
    }

    public function asyncGetUserProfile(int $id): array
    {
        return User::find($id)->getAttributes();
    }

    public function createNewUser(RegisterRequest $request): void
    {
        $dto = new UserProfileDTO(
            email: $request->getEmail(),
            name: $request->getName(),
            password: Hash::make($request->getPassword()),
            language: Language::fromCode(app()->getLocale())->value,
        );

        $this->service->create($dto);
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

    public function asyncGetUserInfo(int $id = 0): array
    {
        return ['info' => User::loadBy($id)];
    }

    public function remove(int $id): void
    {
        $this->service->deleteUser(User::findOrFail($id));
    }

    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (UserInfoFilter::FIELDS as $item) {
            if (!is_null($request->get($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('profiles.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('profiles.list');
    }
}
