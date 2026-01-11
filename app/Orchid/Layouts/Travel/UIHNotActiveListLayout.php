<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use App\Helpers\System\MrDateTime;
use App\Models\Travel\UIT;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class UIHNotActiveListLayout extends Table
{
    public $target = 'not-active-uih';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('user_id', 'User')->render(fn(UIT $uih) => $uih->getUser()->name),
            TD::make('user_id', 'Email')->render(fn(UIT $uih) => $uih->getUser()->email),
            TD::make('created_at', 'Created')->sort()
                ->render(fn(UIT $uih) => $uih->created_at),
            TD::make('updated_at', 'Updated')->sort()
                ->render(fn(UIT $uih) => $uih->updated_at),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(UIT $uih) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the user in travel?'))
                            ->method('removeUIH', ['id' => $uih->id]),
                    ])),
        ];
    }
}
