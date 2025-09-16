<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Catalog;

use App\Models\Catalog\Manufacturer;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ManufacturerListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('name')->sort(),
            TD::make('address', 'Адрес')->sort(),
            TD::make('created_at', 'Created')->render(fn(Manufacturer $manufacturer) => $manufacturer->created_at->format('d.m.Y'))->sort(),

            TD::make('#', '#')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (Manufacturer $manufacturer) {
                    return DropDown::make()->icon('options-vertical')->list([
                        ModalToggle::make('изменить')->modal('edit_manufacturer_modal')
                            ->modalTitle('Изменить производителя')
                            ->method('saveManufacturer')
                            ->parameters(['manufacturer_id' => $manufacturer->id()])
                            ->icon('pencil'),

                        Button::make('Delete')
                            ->icon('trash')
                            ->confirm('This item will be removed permanently.')
                            ->method('remove', [
                                'manufacturer_id' => $manufacturer->id(),
                            ]),
                    ]);
                }),
        ];
    }
}
