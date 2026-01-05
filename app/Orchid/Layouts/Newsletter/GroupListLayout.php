<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Newsletter;

use App\Models\News\NewsGroup;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use function Termwind\render;

class GroupListLayout extends Table
{
    public $target = 'group-list';

    protected $title = 'Группы новостей';

    protected function columns(): iterable
    {
        return [
            TD::make('active', 'Активно')->active(),
            TD::make('language', 'Language')->render(fn(NewsGroup $group) => $group->getLanguage()->getLabel()),
            TD::make('code', 'Код'),
            TD::make('title', 'Наименование')->render(function (NewsGroup $group) {
                return Link::make($group->getTitle())->route('newsletter.news.list', ['group_id' => $group->id()]);
            }),
            TD::make('updated_at', 'Обновлено')->render(fn($group) => $group->updated_at?->format('d.m.Y h:i')),

            TD::make('#', 'Действия')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (NewsGroup $group) {
                    return DropDown::make()->icon('options-vertical')->list([
                        ModalToggle::make('Изменить')
                            ->icon('pencil')
                            ->modal('news_group_modal')
                            ->modalTitle('Группа')
                            ->method('saveGroup')
                            ->asyncParameters(['group_id' => $group->id()]),

                        Button::make('Удалить')->icon('trash')
                            ->confirm('Вы уверены, что хотите удалить группу? Будут также удалены все новости, связанные с этой группой.')
                            ->method('deleteGroup', ['group_id' => $group->id()]),
                    ]);
                }),
        ];
    }
}
