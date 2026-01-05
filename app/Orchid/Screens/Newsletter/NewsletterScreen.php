<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Newsletter;

use App\Models\News\NewsGroup;
use App\Models\News\NewsSubgroup;
use App\Orchid\Filters\Newsletter\NewsFilter;
use App\Orchid\Layouts\Newsletter\AddNewsEditLayout;
use App\Orchid\Layouts\Newsletter\GroupEditLayout;
use App\Orchid\Layouts\Newsletter\GroupListLayout;
use App\Orchid\Layouts\Newsletter\NewsListLayout;
use App\Orchid\Layouts\Newsletter\SubgroupEditLayout;
use App\Orchid\Layouts\Newsletter\SubgroupListLayout;
use App\Services\Newsletter\NewsService;
use App\Services\System\Enum\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class NewsletterScreen extends Screen
{
    public function __construct(
        private readonly Request     $request,
        private readonly NewsService $newsService
    ) {}

    public string $name = 'Новости';

    public function query(): iterable
    {
        return [
            'group-list' => NewsGroup::when(!empty($this->request->get('language')), function ($query) {
                $query->where('language', (int)$this->request->get('language'));
            })->get(),

            'subgroup-list' => NewsSubgroup::join(NewsGroup::getTableName(), NewsSubgroup::getTableName() . '.group_id', '=', NewsGroup::getTableName() . '.id')
                ->when($this->request->get('group_id'), fn($query) => $query->where('group_id', $this->request->get('group_id')))
                ->when(!empty($this->request->get('language')), function ($query) {
                    $query->where('language', (int)$this->request->get('language'));
                })
                ->get(NewsSubgroup::getTableName() . '.*'),

            'list' => NewsFilter::runQuery(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('группа')
                ->icon('plus')
                ->class('mr-btn-success')
                ->modal('news_group_modal')
                ->modalTitle('Группа новостей')
                ->method('saveGroup')
                ->asyncParameters(['group_id' => 0]),

            ModalToggle::make('рубрика')
                ->icon('plus')
                ->class('mr-btn-success')
                ->modal('news_subgroup_modal')
                ->modalTitle('Раздел внутри группы новостей')
                ->method('saveSubgroup')
                ->asyncParameters(['subgroup_id' => 0]),

            ModalToggle::make('новость')
                ->icon('plus')
                ->class('mr-btn-success')
                ->modal('add_news')
                ->modalTitle('Добавить')
                ->method('saveNews'),
        ];
    }

    public function layout(): iterable
    {
        return [
            NewsFilter::displayFilterCard($this->request),

            Layout::split([
                GroupListLayout::class,
                SubgroupListLayout::class,
            ]),

            NewsListLayout::class,

            Layout::modal('news_group_modal', GroupEditLayout::class)->async('asyncGetGroup'),
            Layout::modal('add_news', AddNewsEditLayout::class)->size(Modal::SIZE_LG),
            Layout::modal('news_subgroup_modal', SubgroupEditLayout::class)->async('asyncGetSubgroup'),
        ];
    }

    public function asyncGetGroup(int $group_id = 0): array
    {
        return ['group' => NewsGroup::loadBy($group_id) ?: new NewsGroup()];
    }

    public function asyncGetSubgroup(int $subgroup_id = 0): array
    {
        return ['subgroup' => NewsSubgroup::loadBy($subgroup_id)];
    }

    public function saveSubgroup(request $request, int $subgroup_id): void
    {
        $input = Validator::make($request->all(), [
            'subgroup.group_id' => 'required|integer',
            'subgroup.title'    => 'required|max:255',
            'subgroup.code'     => 'required|max:255',
        ])->validate()['subgroup'];

        $this->newsService->saveSubgroup($subgroup_id, $input);
    }

    public function saveNews(request $request): RedirectResponse
    {
        $input = Validator::make($request->all(), [
            'news.active'       => 'boolean',
            'news.public'       => 'boolean',
            'news.group_id'     => 'required|integer|exists:news_groups,id',
            'news.language'     => 'required|integer|in:' . implode(',', array_keys(Language::getSelectList())),
            'news.published_at' => 'nullable|date',
            'news.title'        => 'required|max:1000',
            'news.code'         => 'required|max:255|unique:news,code',
        ])->validate()['news'];

        $input['language'] = Language::from((int)$input['language'])->value;
        $id = $this->newsService->saveNews(0, $input);

        Toast::info('Новость сохранена')->delay(1000);

        return redirect()->route('newsletter.news.edit', ['news_id' => $id]);
    }

    public function saveGroup(request $request, int $group_id): void
    {
        $input = Validator::make($request->all(), [
            'group.title'    => 'required|max:255',
            'group.active'   => 'required|boolean',
            'group.code'     => 'nullable|max:255',
            'group.language' => 'required|integer|in:' . implode(',', array_keys(Language::getSelectList())),
        ])->validate()['group'];

        if ($group_id === 0 && empty($input['code'])) {
            $input['code'] = Str::slug($input['title']);
        }

        $this->newsService->saveGroup($group_id, $input);

        Toast::info('Группа новостей сохранена')->delay(1500);
    }

    public function deleteGroup(int $group_id): void
    {
        $this->newsService->deleteGroup($group_id);

        Toast::info('Группа новостей удалена')->delay(1500);
    }

    public function deleteSubgroup(int $subgroup_id): void
    {
        $this->newsService->deleteSubgroup($subgroup_id);
    }

    public function deleteNews(int $news_id): void
    {
        $this->newsService->deleteNews($news_id);

        Toast::info('Новость удалена')->delay(1500);
    }

    public function getGroupSelectList(): array
    {
        $list = [];
        foreach ($this->newsService->getGroupList() as $group) {
            $list[$group->id] = $group->name;
        }

        return $list;
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (NewsFilter::getFilterFields() as $item) {
            if (!is_null($request->get($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('newsletter.news.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('newsletter.news.list');
    }
    #endregion
}
