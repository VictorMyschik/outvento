<?php

namespace App\Orchid\Layouts\Newsletter;

use App\Models\News\NewsGroup;
use App\Models\News\NewsSubgroup;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class SubgroupListLayout extends Table
{
    public $target = 'subgroup-list';

    protected $title = 'Рубрики (разделы) новостей в группах';

    protected function columns(): iterable
    {
        return [
            TD::make('language', 'Language')->render(fn(NewsSubgroup $subgroup) => $subgroup->getGroup()->getLanguage()->getLabel()),
            TD::make('name', 'Наименование')->render(function (NewsSubgroup $subgroup) {
                return Link::make($subgroup->title)->route('newsletter.news.list', ['subgroup_id' => $subgroup->id()]);
            }),

            TD::make('group_id', 'Группа новостей')
                ->render(function (NewsSubgroup $subgroup) {
                    return $subgroup->getGroup()->getTitle();
                }),

            TD::make('#', 'Действия')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (NewsSubgroup $subgroup) {
                    return DropDown::make()->icon('options-vertical')->list([
                        ModalToggle::make('Изменить')
                            ->icon('pencil')
                            ->modal('news_subgroup_modal')
                            ->modalTitle('Раздел группы')
                            ->method('saveSubgroup')
                            ->asyncParameters(['subgroup_id' => $subgroup->id()]),

                        Button::make('Удалить')->icon('trash')
                            ->confirm('Вы уверены, что хотите удалить раздел? Будут также удалены все новости, связанные с этим разделом.')
                            ->method('deleteSubgroup', ['subgroup_id' => $subgroup->id()]),
                    ]);
                }),
        ];
    }
}
