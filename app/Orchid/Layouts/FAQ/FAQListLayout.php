<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\FAQ;

use App\Models\Faq;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class FAQListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('active', 'Active')->active()->sort(),
            TD::make('language_id', 'Language')->render(fn(Faq $faq) => $faq->getLanguage()->getLabel())->sort(),
            TD::make('title', 'Title')->sort(),
            TD::make('text', 'Text')->sort()->render(function (Faq $faq) {
                return $faq->getText();
            }),
            TD::make('created_at', 'Created')
                ->render(fn(Faq $faq) => $faq->created_at->format('d.m.Y'))
                ->sort()
                ->defaultHidden(),
            TD::make('updated_at', 'Updated')
                ->render(fn(Faq $faq) => $faq->updated_at?->format('d.m.Y'))
                ->sort()
                ->defaultHidden(),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(Faq $faq) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
                            ->icon('pencil')
                            ->modal('faq_modal')
                            ->modalTitle('Edit FAQ id ' . $faq->id)
                            ->method('saveFAQ')
                            ->asyncParameters(['id' => $faq->id]),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the faq?'))
                            ->method('remove', ['id' => $faq->id]),
                    ])),
        ];
    }

    public function hoverable(): true
    {
        return true;
    }
}
