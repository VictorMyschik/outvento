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
            TD::make('category')->sort(),
            TD::make('', 'Title')->render(fn(Settings $setup) => $setup->getCodeKey()->getLabel()),
            TD::make('code_key', 'Code')->sort(),
            TD::make('value', 'Value'),
            TD::make('description', 'Description'),
            TD::make('created_at', 'Created')
                ->render(fn(Settings $setup) => $setup->created_at->format('d.m.Y'))
                ->sort(),
            TD::make('updated_at', 'Updated')
                ->render(fn(Settings $setup) => $setup->updated_at?->format('d.m.Y'))
                ->sort(),

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

    public function hoverable(): bool
    {
        return true;
    }

    public function compact(): bool
    {
        return true;
    }
}
