<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Newsletter;

use App\Models\News\News;
use App\Services\Newsletter\NewsService;
use App\Services\References\Enum\ImageTypeEnum;
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
           /* TD::make('#', 'Image')->render(function (News $news) {
                $imageName = Image::where('type', ImageTypeEnum::News->value)->where('object_id', $news->id())->latest()->first();
                if ($imageName) {
                    $path = Storage::url(ImageTypeEnum::News->value . '/' . $imageName->getName());
                    return View('admin.image')->with(['path' => $path]);
                }
                return null;
            }),*/
            TD::make('language', 'Язык')->render(fn(News $news) => $news->getLanguage()->getLabel())->sort(),
            TD::make('group_id', 'Группа')->render(function (News $news) {
                if ($news->getGroupId()) {
                    $title = $this->newsService->getGroupById($news->getGroupId())->getTitle();
                    return Link::make($title)->route('newsletter.news.list', ['group_id' => $news->getGroupId()]);
                }

                return '';
            })->sort(),
            TD::make('title', 'Наименование')->render(function (News $news) {
                return Link::make($news->getTitle())->route('newsletter.news.edit', ['news_id' => $news->id()]);
            })->sort(),
            TD::make('created_at', 'Создано')->render(fn(News $news) => $news->created_at->format('d.m.Y'))->sort(),
            TD::make('updated_at', 'Обновлено')->render(fn(News $news) => $news->updated_at?->format('d.m.Y h:i'))->sort(),

            TD::make('#', 'Действия')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (News $news) {
                    return DropDown::make()->icon('options-vertical')->list([
                        Button::make('Удалить')
                            ->icon('trash')
                            ->confirm('Удалить новость?')
                            ->method('deleteNews', [
                                'news_id' => $news->id(),
                            ]),
                    ]);
                }),
        ];
    }
}
