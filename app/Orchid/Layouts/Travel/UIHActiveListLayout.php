<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use App\Helpers\System\MrDateTime;
use App\Models\UIH;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class UIHActiveListLayout extends Table
{
    public $target = 'active-uih';

    public function columns(): array
    {
        $user = Auth::user();

        $out[] = TD::make('id', __('ID'))->sort();

        $out[] = TD::make('user_id', 'User')->render(fn(UIH $uih) => $uih->getUser()->name);
        $out[] = TD::make('user_id', 'Email')->render(fn(UIH $uih) => $uih->getUser()->email);

        $out[] = TD::make('created_at', 'Created')->sort()
            ->render(fn(UIH $client) => $client->getCreatedObject()->format(MrDateTime::SHORT_DATE));
        $out[] = TD::make('updated_at', 'Updated')->sort()
            ->render(fn(UIH $client) => $client->getUpdatedObject()?->format(MrDateTime::SHORT_DATE));


        $out[] = TD::make(__('Actions'))
            ->align(TD::ALIGN_CENTER)
            ->width('100px')
            ->render(function (UIH $uih) {

                $btnBan = Button::make(__('ban'))
                    ->icon('ban')
                    ->confirm(__('Are you sure you want to decline the user in travel?'))
                    ->method('declineUIH', ['id' => $uih->id]);

                return DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        $btnBan,

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the user in travel?'))
                            ->method('removeUIH', ['id' => $uih->id]),
                    ]);
            });

        return $out;
    }
}
