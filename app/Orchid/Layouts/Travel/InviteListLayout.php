<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use App\Models\TravelInvite;
use App\Services\Travel\Enum\TravelInviteStatus;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class InviteListLayout extends Table
{
    public $target = 'travel_invites';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('email', 'User')->render(function (TravelInvite $invite) {
                $out = $invite->email;
                if ($user = $invite->getUser()) {
                    $out = $user->id . ' | ' . '<a href="' . route('platform.systems.users.edit', $user->id) . '">' . $user->name . '</a>' . ' | ' . $invite->email;
                }

                return $out;
            }),
            TD::make('status', 'Status')->render(fn(TravelInvite $invite) => TravelInviteStatus::from($invite->status)->getLabel()),

            TD::make('created_at', 'Created')->sort()->render(fn(TravelInvite $invite) => $invite->created_at->format('d.m.Y H:i:s')),
            TD::make('updated_at', 'Updated')->sort()->render(fn(TravelInvite $invite) => $invite->updated_at?->format('d.m.Y H:i:s')),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(TravelInvite $invite) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Button::make(__('Resend'))
                            ->icon('refresh')
                            ->confirm(__('Resend this invite?'))
                            ->method('resendTravelInvite', ['inviteId' => $invite->id]),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Delete this invite?'))
                            ->method('removeTravelInvite', ['inviteId' => $invite->id]),
                    ])),
        ];
    }
}
