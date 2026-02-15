<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Newsletter;

use App\Models\News\News;
use App\Services\Newsletter\NewsService;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class NewsListLayout extends Table
{
    public $target = 'list';

    public function __construct(private readonly NewsService $newsService) {}

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('active', 'Активно')->active()->sort(),
            TD::make('public', 'Опубликовано')->active()->sort(),
            TD::make('#', 'Image')->render(function (News $news) {
                if ($news->path) {
                    $path = Storage::url($news->path);
                    return View('admin.image')->with(['path' => $path]);
                }
                return null;
            }),
            TD::make('language', 'Язык')->render(fn(News $news) => $news->getLanguage()->getLabel())->sort(),
            TD::make('group_id', 'Группа')->render(function (News $news) {
                if ($news->getGroupId()) {
                    $title = $this->newsService->getGroupById($news->getGroupId())->getTitle();
                    return Link::make($title)->route('newsletter.news.list', ['group_id' => $news->getGroupId()]);
                }

                return '';
            })->sort(),
            TD::make('title', 'Наименование')->render(function (News $news) {
                return Link::make($news->getTitle())->stretched()->route('newsletter.news.edit', ['news_id' => $news->id()]);
            })->sort(),
            TD::make('created_at', 'Создано')->render(fn(News $news) => $news->created_at->format('d.m.Y'))->sort(),
            TD::make('updated_at', 'Обновлено')->render(fn(News $news) => $news->updated_at?->format('d.m.Y h:i'))->sort(),
        ];
    }

    public function hoverable(): true
    {
        return true;
    }
}
