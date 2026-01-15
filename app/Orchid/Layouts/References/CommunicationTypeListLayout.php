<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\References;

use App\Models\Travel\TravelType;
use App\Models\UserInfo\CommunicationType;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class CommunicationTypeListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('#', 'Image')->render(function (CommunicationType $communicationType) {
                return View('admin.image')->with(['path' => $communicationType->getImageUrl()]);
            }),
            TD::make('name_ru', 'RU')->sort(),
            TD::make('name_en', 'EN')->sort(),
            TD::make('name_pl', 'PL')->sort(),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(CommunicationType $communicationType) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
                            ->icon('pencil')
                            ->modal('communication_type')
                            ->modalTitle('Edit type id ' . $communicationType->id)
                            ->method('saveCommunicationType')
                            ->asyncParameters(['id' => $communicationType->id]),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the communication type?'))
                            ->method('remove', ['id' => $communicationType->id]),
                    ])),
        ];
    }
}
