<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Http\Controllers\API\Travel\Request\CreateTravelRequest;
use App\Models\Travel\Travel;
use App\Models\User;
use App\Orchid\Layouts\User\Travel\AddTravelModalLayout;
use App\Orchid\Layouts\User\UserBaseScreen;
use App\Services\Travel\TravelService;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;

class UserTravelDetailsScreen extends UserBaseScreen
{
    public ?User $user = null;
    public ?Travel $travel = null;

    public function name(): string
    {
        return $this->travel->title ?? '';
    }

    public function description(): string
    {
        return $this->travel->preview ?? '';
    }

    public function query(User $user, ?Travel $travel = null): iterable
    {
        return [
            'user'   => $user,
            'travel' => $travel,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Назад')->class('mr-btn mr-btn-route')->icon('arrow-up')->route('profiles.travels', ['user' => $this->user->id]),
        ];
    }

    public function layout(): iterable
    {
        $out[] = Layout::modal('add_travel_modal', AddTravelModalLayout::class);

        return $out;
    }

    public function saveUserTravel(CreateTravelRequest $request, TravelService $travelService): RedirectResponse
    {
        $input = $request->getInput();
        $input['user_id'] = $this->user->id;

        $id = $travelService->createTravel($input);

        return redirect()->route('platform.users.travels', ['user' => $this->user->id]);
    }
}