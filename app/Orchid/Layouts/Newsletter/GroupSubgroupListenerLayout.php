<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Newsletter;

use App\Models\News\News;
use App\Models\News\NewsGroup;
use App\Models\News\NewsSubgroup;
use App\Services\System\Enum\Language;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class GroupSubgroupListenerLayout extends Listener
{
    protected $targets = [
        'news.group_id',
        'news.language',
    ];

    protected function layouts(): iterable
    {
        $news = $this->query->get('news');

        if (!$news instanceof News) {
            $news = News::find($this->query->get('news.id'));
        }

        $groupOptions = NewsGroup::where('language', (int)$this->query->get('news.language'))->get()->pluck('title', 'id')->toArray();
        $oldGroupSelectedId = $this->query->get('news.group_id');
        $groupSelectedId = $groupOptions[$oldGroupSelectedId] ?? array_key_first($groupOptions);

        $isPublic = $news->isPublic();
        $createdAt = $news->created_at->format('d.m.Y');

        $links = $news->getUriList();

        $subgroupSelectedList = [];
        foreach ($news->getSubgroupList() ?? [] as $subgroup) {
            $subgroupSelectedList[] = $subgroup->id();
        }

        return [
            Layout::rows([
                Input::make('news.id')->type('hidden'),
                ViewField::make('')->view('admin.h6')->value($news->getTextVisible($links))->class('text-muted mb-0'),
                Group::make([
                    Switcher::make('news.active')->sendTrueOrFalse()->title('В поиске'),
                    Switcher::make('news.public')->sendTrueOrFalse()->value($isPublic)->title('Опубликовано'),
                    Input::make('news.code')->popover('Оставьте пустым, чтобы сгенерировать на основе имени статьи')->type('text')->max(255)->title('Код'),
                    Select::make('news.language')->required()->options(Language::getSelectList())->title('Язык')
                ])->fullWidth(),

                Group::make([
                    Select::make('news.group_id')
                        ->required()
                        ->value($groupSelectedId)
                        ->options($groupOptions)
                        ->title('Группа'),
                    Select::make('news.subgroups')->empty('[без рубрики]')
                        ->options(NewsSubgroup::whereIn('group_id', array_keys($groupOptions))->get()->pluck('title', 'id')->toArray())
                        ->multiple()
                        ->value($subgroupSelectedList)
                        ->title('Рубрика'),
                ]),

                Group::make([
                    DateTimer::make('news.published_at')
                        ->title('Дата публикации. Оставьте пустым, что бы опубликовать сразу (дата создания: ' . $createdAt . ')')
                        ->format('Y-m-d')
                ])->fullWidth(),

                Input::make('news.title')->required()->name('news.title')->title('Заголовок'),
            ])
        ];
    }

    public function handle(Repository $repository, Request $request): Repository
    {
        return $repository
            ->set('news.id', $request->input('news.id'))
            ->set('news.active', $request->input('news.active'))
            ->set('news.public', $request->input('news.public'))
            ->set('news.code', $request->input('news.code'))
            ->set('news.language', $request->input('news.language'))
            ->set('news.group_id', $request->input('news.group_id'))
            ->set('news.subgroup_id', $request->input('news.subgroup_id'))
            ->set('news.published_at', $request->input('news.published_at'))
            ->set('news.title', $request->input('news.title'));
    }
}
