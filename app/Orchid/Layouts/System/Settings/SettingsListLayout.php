<?php

namespace App\Orchid\Layouts\System\Settings;

use App\Models\System\Settings;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class SettingsListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('active', 'Active')->sort()->active(),
            TD::make('category')->sort()->defaultHidden(),
            TD::make('name')->sort(),
            TD::make('code_key')->sort()->defaultHidden(),
            TD::make('value', 'Value')->width('50%'),
            TD::make('description', 'Description')->width('50%')->defaultHidden(),
            TD::make('created_at', 'Created')
                ->render(fn (Settings $setup) => $setup->created_at->format('d.m.Y'))
                ->sort()
                ->defaultHidden(),
            TD::make('updated_at', 'Updated')
                ->render(fn (Settings $setup) => $setup->updated_at?->format('d.m.Y'))
                ->sort()
                ->defaultHidden(),

            TD::make('#', 'Действия')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (Settings $setup) {
                    return DropDown::make()->icon('options-vertical')->list([
                        ModalToggle::make('Edit')
                            ->icon('pencil')
                            ->modal('setup_modal')
                            ->modalTitle('Settings')
                            ->method('saveSettings')
                            ->asyncParameters(['id' => $setup->id()]),

                        Button::make('Delete')
                            ->icon('trash')
                            ->confirm('This item will be removed permanently.')
                            ->method('remove', [
                                'id' => $setup->id(),
                            ]),
                    ]);
                }),
        ];
    }
}
