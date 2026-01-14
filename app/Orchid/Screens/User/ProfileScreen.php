<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;

class ProfileScreen extends Screen
{

    public ?User $user = null;

    public function __construct(
        private readonly Request     $request,
        private readonly UserService $service,
    ) {}

    public function name(): string
    {
        return $this->user->getFullName();
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
            Link::make('Назад')->icon('arrow-up')->route('users.list'),
        ];
    }

    public function layout(): iterable
    {
        return [];
    }
}
