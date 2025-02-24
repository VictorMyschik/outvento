<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use App\Helpers\System\MrDateTime;
use App\Models\UIH;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class UIHNotActiveListLayout extends Table
{
    public $target = 'not-active-uih';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('user_id', 'User')->render(fn(UIH $uih) => $uih->getUser()->name),
            TD::make('user_id', 'Email')->render(fn(UIH $uih) => $uih->getUser()->email),
            TD::make('created_at', 'Created')->sort()
                ->render(fn(UIH $uih) => $uih->getCreatedObject()->format(MrDateTime::SHORT_DATE)),
            TD::make('updated_at', 'Updated')->sort()
                ->render(fn(UIH $uih) => $uih->getUpdatedObject()?->format(MrDateTime::SHORT_DATE)),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(UIH $uih) => DropDown::make()
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
