<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Http\Controllers\API\Travel\Request\CreateTravelRequest;
use App\Models\User;
use App\Orchid\Filters\User\UserTravelFilter;
use App\Orchid\Layouts\User\Travel\AddTravelModalLayout;
use App\Orchid\Layouts\User\Travel\UserTravelListLayout;
use App\Services\Travel\TravelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Support\Facades\Layout;

class UserTravelListScreen extends UserBaseScreen
{
    public ?User $user = null;

    public function name(): string
    {
        return 'Users travels';
    }

    public function description(): string
    {
        return 'ID ' . $this->user->id . (($this->user->getFullName() ? ' | ' . $this->user->getFullName() : '') ?: ' ' . $this->user->name . ' |   ' . $this->user->email);
    }

    public function query(User $user): iterable
    {
        return [
            'user' => $user,
            'list' => UserTravelFilter::runQuery($user->id)->paginate(50),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Travel')
                ->icon('plus')
                ->class('mr-btn-success pull-left')
                ->modal('add_travel_modal')
                ->modalTitle('User travel')
                ->method('saveUserTravel')
                ->asyncParameters(['id' => $this->user->id]),
            Link::make('Назад')->class('mr-btn mr-btn-route')->icon('arrow-up')->route('profiles.details', ['user' => $this->user->id]),
        ];
    }

    public function layout(): iterable
    {
        $out[] = UserTravelFilter::displayFilterCard(request());
        $out[] = UserTravelListLayout::class;
        $out[] = Layout::modal('add_travel_modal', AddTravelModalLayout::class);

        return $out;
    }

    public function saveUserTravel(CreateTravelRequest $request, TravelService $travelService): RedirectResponse
    {
        $input = $request->getInput();
        $input['language'] = $this->user->language;

        $id = $travelService->createTravel($this->user->id, $input);

        return redirect()->route('profiles.travel.details', ['user' => $this->user->id, 'travel' => $id]);
    }

    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (UserTravelFilter::FIELDS as $item) {
            if (!is_null($request->input($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('profiles.travels', $list + ['user' => $this->user->id]);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('profiles.travels', ['user' => $this->user->id]);
    }
}