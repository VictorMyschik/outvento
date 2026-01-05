<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Newsletter;

use App\Models\News\NewsGroup;
use App\Services\System\Enum\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class AddNewsEditLayout extends Listener
{
    protected $targets = [
        'news.title',
        //'news.active',
        //'news.public',
        //'news.group_id',
        'news.language',
        //'news.published_at',
        //'news.title',
    ];

    protected function layouts(): iterable
    {
        $defaultLanguage = array_key_first(Language::getSelectList());

        return [
            Layout::rows([
                Group::make([
                    Select::make('news.language')->required()->options(Language::getSelectList())->title('Язык'),
                    Select::make('news.group_id')->required()
                        ->fromQuery(NewsGroup::where('language', (int)$this->query->get('news.language') ?: $defaultLanguage), 'title', 'id')
                        ->title('Группа'),
                ])->fullWidth(),

                Group::make([
                    DateTimer::make('news.published_at')
                        ->title('Дата публикации. Оставьте пустым, что бы опубликовать сразу (дата создания: ' . now()->format('d.m.Y') . ')')
                        ->format('Y-m-d')
                ])->fullWidth(),

                Input::make('news.title')->required()->name('news.title')->title('Заголовок'),
                Input::make('news.code')->popover('Оставьте пустым, чтобы сгенерировать на основе имени статьи')->type('text')->max(255)->title('Код'),
            ]),
        ];
    }

    public function handle(Repository $repository, Request $request): Repository
    {
        return $repository
            ->set('news.code', Str::slug($request->input('news.title')))
            ->set('news.title', $request->input('news.title'))
            ->set('news.group_id', $request->input('news.group_id'))
            ->set('news.language', $request->input('news.language'))
            ->set('news.published_at', $request->input('news.published_at'));
    }
}
