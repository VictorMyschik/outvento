<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Newsletter;

use App\Models\Catalog\CatalogGood;
use App\Models\Catalog\CatalogGroup;
use App\Models\News\News;
use App\Models\News\NewsAdditional;
use App\Models\News\NewsGroup;
use App\Models\Orchid\Attachment;
use App\Orchid\Enums\ConstructorObjectTypeEnum;
use App\Orchid\Layouts\Newsletter\AddGoodLayout;
use App\Orchid\Layouts\Newsletter\AddGroupLayout;
use App\Orchid\Layouts\Newsletter\CatalogGoodSortEditLayout;
use App\Orchid\Layouts\Newsletter\GroupSubgroupListenerLayout;
use App\Orchid\Layouts\Newsletter\NewsUploadEditLayout;
use App\Orchid\Screens\Traits\ConstructorTrait;
use App\Services\Catalog\CatalogService;
use App\Services\Constructor\ConstructorService;
use App\Services\Newsletter\Enum\NewsAdditionalTypeEnum;
use App\Services\Newsletter\NewsService;
use App\Services\System\Enum\Language;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Layouts\Tabs;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class NewsEditScreen extends Screen
{
    use ConstructorTrait;

    private ?News $news = null;

    public function __construct(
        private readonly NewsService        $service,
        private readonly CatalogService     $catalogService,
        private readonly ConstructorService $constructorService,
    ) {}

    public function name(): ?string
    {
        return $this->news?->getTitle();
    }

    public function description(): ?string
    {
        $group = $this->news?->getGroupId() ? NewsGroup::loadBy($this->news->getGroupId())->title : 'Без группы';
        return $group . ' | ' . View('admin.created_updated', ['value' => $this->news])->toHtml();
    }

    public function query(int $news_id): iterable
    {
        $this->news = News::loadBy($news_id);

        return [
            'news' => $this->news,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Сохранить')
                ->method('saveNews')
                ->class('mr-btn-primary')
                ->parameters(['news_id' => $this->news?->id() ?: 0])
                ->icon('check'),
            Link::make('Назад')
                ->icon('arrow-up')
                ->class('mr-btn-primary')
                ->href(request()->headers->get('referer') ?: route('newsletter.news.list')),
        ];
    }

    public function layout(): iterable
    {
        if ($this->news) {
            $out[] = Layout::split([
                GroupSubgroupListenerLayout::class,
                $this->getAdditionalLayout(),
            ]);
        }

        if ($this->news) {
            $out[] = Layout::rows($this->getConstructorLayout($this->news, ConstructorObjectTypeEnum::News, $this->news?->getLanguage(), false));
        }

        $out[] = Layout::rows([
            Group::make([
                Button::make('Clear')->confirm('Удалить?')->class('btn btn-sm')
                    ->name('Удалить новость')
                    ->method('remove')
                    ->class('mr-btn-danger')
                    ->novalidate(),
            ])->autoWidth()
        ]);

        $out[] = Layout::modal('add-good', AddGoodLayout::class)->async('asyncGetGoodOptions')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('add-group', AddGroupLayout::class)->async('asyncGetGroupOptions')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('upload_good_photo', NewsUploadEditLayout::class)->async('asyncGetNewsPhoto');
        $out[] = Layout::modal('sort_object', CatalogGoodSortEditLayout::class)->async('asyncGetGoodSort');

        return array_merge($this->getConstructorPopupLayout(), $out);
    }

    public function asyncGetGoodSort(int $news_id = 0, int $id = 0, int $type = 0): array
    {
        return ['sort' => NewsAdditional::where('id', $id)->value('sort')];
    }

    private function getAdditionalLayout(): Tabs
    {
        return Layout::tabs([
            'Изображение' => $this->photoTab(),
            //'Продукты'    => $this->photoTab(),
        ]);
    }

    private function photoTab(): Rows
    {
        $hasLogo = (bool)$this->service->getLogo($this->news->id());

        return Layout::rows([
            Upload::make('news.logo')->groups('photo')->maxFiles(1)
                ->path('news')
                ->title('Изображение')
                ->set('oldlogo', $this->news?->getLogo()?->getUrl()),
            Button::make('Удалить изображение')
                ->method('removeLogo')
                ->hidden(!$hasLogo)
                ->confirm('Вы уверены, что хотите удалить изображение?')
                ->parameters(['id' => $this->news?->id()]),
        ]);
    }

    public function deleteObjectFromNews(int $id, int $news_id): void
    {
        $this->service->deleteObjectFromNews($id, $news_id);
    }

    public function saveNewsSort(Request $request, int $id, int $news_id): void
    {
        $this->service->updateNewsAdditionalSort($id, $news_id, (int)$request->get('sort'));
    }

    public function addGood(int $news_id, Request $request): void
    {
        foreach ($request->get('good_ids', []) as $good_id) {
            $this->service->addGoodOrGroup($news_id, $good_id, NewsAdditionalTypeEnum::GOOD);
        }
    }

    public function addGroup(int $news_id, Request $request): void
    {
        foreach ($request->get('group_ids', []) as $group_id) {
            $this->service->addGoodOrGroup($news_id, $group_id, NewsAdditionalTypeEnum::GROUP);
        }
    }

    public function removeAllGoods(int $news_id): void
    {
        $this->service->removeAllGoods($news_id);
    }

    public function asyncGetGoodOptions(int $news_id = 0, int $language = 0): array
    {
        $goodListIds = $this->service->getGoodsOrGroupsIds($news_id, NewsAdditionalTypeEnum::GOOD);
        $ids = [];
        foreach ($goodListIds as $id) {
            $ids[] = $id->relation_object_id;
        }
        $options = DB::table(CatalogGood::getTableName())
            ->where('language', $language)
            ->whereNotIn('good_id', $ids)
            ->pluck('name', 'good_id')
            ->all();

        return ['options' => $options];
    }

    public function asyncGetGroupOptions(int $news_id = 0, int $language = 0): array
    {
        $groups = $this->service->getGoodsOrGroupsIds($news_id, NewsAdditionalTypeEnum::GROUP);
        $groupsIds = [];
        foreach ($groups as $group) {
            $groupsIds[] = $group->relation_object_id;
        }
        $options = DB::table(CatalogGroup::getTableName())
            ->whereNotIn('id', $groupsIds)
            ->pluck('name', 'id')
            ->all();

        return ['options' => $options];
    }

    public function saveNews(int $news_id, Request $request): RedirectResponse
    {
        $input = $request->all()['news'];
        $subgroups = $input['subgroups'] ?? [];

        if ($news_id === 0 || empty($input['code'])) {
            $input['code'] = Str::slug($input['title']);
        }

        $input['language'] = Language::from((int)$input['language'])->value;

        try {
            if (!empty($input['logo'])) {
                $attachment = Attachment::loadByOrDie((int)$input['logo'][0]);
                $path = Storage::path($attachment->getFullPath());
                if (!file_exists($path) || !is_file($path)) {
                    Attachment::where('hash', $attachment->getHash())->delete();
                    throw new Exception('Ошибка при загрузке файла. Попробуйте ещё раз.');
                }
                $input['logo'] = new UploadedFile($path, $attachment->getOriginalName(), $attachment->getMime(), null, true);
            }
            $subgroups && $input['subgroups'] = [$subgroups];
            $input['active'] = ((bool)$input['public'] === false) ? false : (bool)$input['active'];
            $input['public'] = ((bool)$input['active'] === true) ? true : (bool)$input['public'];
            $news_id = $this->service->saveNews($news_id, $input);

            if ($attachment ?? null) {
                $attachment->delete();
            }

            Toast::success('Партнёр сохранён')->delay(1000);
        } catch (Exception $e) {
            Toast::error($e->getMessage())->delay(1000);
            throw $e;
        }

        Toast::info('Новость сохранена')->delay(1000);

        return redirect()->route('newsletter.news.edit', ['news_id' => $news_id]);
    }

    public function removeLogo(int $id): void
    {
        $this->service->removeLogo($id);
    }

    public function remove(int $news_id): RedirectResponse
    {
        $this->service->deleteNews($news_id);

        Toast::info('Новость удалена')->delay(1000);

        return redirect()->route('newsletter.news.list');
    }
}
