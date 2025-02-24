<?php

namespace App\Orchid\Layouts\Language;

use App\Models\System\Language;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class LanguageListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('active', __('Active'))->sort()->active(),
            TD::make('code', __('Code'))->sort(),
            TD::make('name', __('Name'))->render(function (Language $language) {
                return Link::make($language->name)->route('language.translate.list', $language->id);
            })->sort(),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(Language $language) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
                            ->type(Color::PRIMARY())
                            ->icon('pencil')
                            ->modal('language')
                            ->modalTitle('Language id ' . $language->id())
                            ->method('saveLanguage')
                            ->asyncParameters(['id' => $language->id()]),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the Language?'))
                            ->method('remove', ['id' => $language->id()]),
                    ])),
        ];
    }
}
