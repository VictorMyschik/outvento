<?php

namespace App\Orchid\Layouts\Forms;

use App\Models\Forms\Form;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class FormRequestListLayout extends Table
{
    public $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('type', 'Тип')->render(fn(Form $form) => $form->getType()->getLabel())->sort(),
            TD::make('language', 'Язык')->render(fn(Form $form) => $form->getLanguage()->getLabel())->sort(),
            TD::make('active', 'Прочитано')->active()->sort(),
            TD::make('contact', 'Обратный адрес')->sort(),
            TD::make('description', 'Свой комментарий')->render(fn(Form $form) => $form->description)->width('300px')->sort(),
            TD::make('created_at', 'Дата заявки')->render(fn(Form $form) => $form->created_at->format('d.m.Y H:i'))->sort(),
            TD::make('updated_at', 'Дата обновления')->defaultHidden()->render(fn(Form $form) => $form->updated_at?->format('d.m.Y H:i'))->sort(),
            TD::make('#', 'Действия')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (Form $form) {
                    return DropDown::make()->icon('options-vertical')->list([
                        ModalToggle::make('комментарий')
                            ->icon('pencil')
                            ->modal('form_modal')
                            ->modalTitle('Оставить свой комментарий')
                            ->method('saveFormComment')
                            ->asyncParameters(['id' => $form->id()]),

                        ModalToggle::make('подробнее')
                            ->icon('eye')
                            ->modal('form_details_modal')
                            ->modalTitle($form->getType()->getLabel() . ' ID' . $form->id())
                            ->asyncParameters(['id' => $form->id()]),

                        Button::make('удалить')
                            ->icon('trash')
                            ->confirm('Этот элемент будет удалён. Удалить?')
                            ->method('deleteForm', ['id' => $form->id()]),
                    ]);
                }),
        ];
    }
}