<?php

declare(strict_types=1);

namespace App\Repositories\Newsletter;

use App\Models\Catalog\CatalogGood;
use App\Models\News\News;
use App\Models\News\NewsAdditional;
use App\Models\News\NewsGroup;
use App\Models\News\NewsInSubgroup;
use App\Models\News\NewsMedia;
use App\Models\News\NewsSubgroup;
use App\Repositories\DatabaseRepository;
use App\Services\Newsletter\Enum\MediaType;
use App\Services\Newsletter\Enum\NewsAdditionalTypeEnum;
use App\Services\Newsletter\Enum\RelationMediaType;
use App\Services\Newsletter\NewsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class NewsRepository extends DatabaseRepository implements NewsRepositoryInterface
{
    public function saveNews(int $newsId, array $data): int
    {
        $subgroups = $data['subgroups'] ?? null;

        if ($newsId > 0) {
            $data['updated_at'] = now();
            unset($data['subgroups']);
            $this->db->table(News::getTableName())->where('id', $newsId)->update($data);
        } else {
            $newsId = $this->db->table(News::getTableName())->insertGetId($data);
        }

        // subgroups
        $this->db->table(NewsInSubgroup::getTableName())->where('news_id', $newsId)->delete();

        if (isset($subgroups)) {
            $rows = [];
            foreach ($subgroups as $subgroupId) {
                $rows[] = [
                    'news_id'     => $newsId,
                    'subgroup_id' => $subgroupId
                ];
            }

            $this->db->table(NewsInSubgroup::getTableName())->insert($rows);
        }

        return $newsId;
    }

    public function deleteNews(int $newsId): void
    {
        $this->db->table(News::getTableName())->where('id', $newsId)->delete();
    }

    public function getGroupList(): array
    {
        return NewsGroup::get()->keyBy('id')->all();
    }

    public function saveGroup(int $groupId, array $data): int
    {
        if ($groupId > 0) {
            $data['updated_at'] = now();
            $this->db->table(NewsGroup::getTableName())->where('id', $groupId)->update($data);
            return $groupId;
        }

        return $this->db->table(NewsGroup::getTableName())->insertGetId($data);
    }

    public function deleteGroup(int $groupId): void
    {
        $this->db->table(NewsGroup::getTableName())->where('id', $groupId)->delete();
    }

    public function getNewsById(int $newsId): ?News
    {
        return News::find($newsId);
    }

    public function searchNews(NewsFilterRequest $filterRequest, int $page = 0, int $perPage = 10, $sort = 'published_at'): LengthAwarePaginator
    {
        $query = $this->db->table(News::getTableName())
            ->where('active', true)
            ->where('public', true);

        $direction = 'ASC';
        if (str_contains($sort, '-')) {
            $sort = str_replace('-', '', $sort);
            $direction = 'DESC';
        }

        if ($groupId = $filterRequest->getGroupId()) {
            // Group can be inactive - we need to check it
            $group = $this->getGroupList()[$groupId] ?? null;
            if (!$group || !$group->isActive()) {
                return new LengthAwarePaginator([], 0, $perPage, $page);
            }

            $query->where('group_id', $groupId);
        }

        if (!empty($filterRequest->getSubgroup())) {
            $query->whereIn(News::getTableName() . '.id', NewsInSubgroup::where('subgroup_id', $filterRequest->getSubgroup())->pluck('news_id'));
        }

        if ($filterRequest->getPublishedAtFrom()) {
            $publishedFrom = (Carbon::createFromDate($filterRequest->getPublishedAtFrom()))->startOfDay()->toDateTimeString();
            $query->where(function ($query) use ($publishedFrom) {
                $query->where('published_at', '>=', $publishedFrom)->orWhereNull('published_at')->where('created_at', '>=', $publishedFrom);
            });
        }

        $publishedTo = Carbon::createFromDate($filterRequest->getPublishedAtTo() ?: now()->toDateString())->endOfDay()->toDateTimeString();
        $query->where(function ($query) use ($publishedTo) {
            $query->where('published_at', '<=', $publishedTo)
                ->orWhereNull('published_at')->where('created_at', '<=', $publishedTo);
        });

        if ($filterRequest->getLanguage()) {
            $query->where('language', $filterRequest->getLanguage()->value);
        }

        if ($filterRequest->getSearch() !== null) {
            $query->where(function ($query) use ($filterRequest) {
                $search = mb_strtolower($filterRequest->getSearch());
                $query->whereRaw('lower(' . News::getTableName() . '.title) like ?', ["%$search%"]);
                $query->orWhereRaw('lower(' . News::getTableName() . '.code) like ?', ["%$search%"]);
            });
        }

        // Catalog Group Filter
        $catalogGroupIds = $filterRequest->getCatalogGroupIds();

        if (count($catalogGroupIds)) {
            $goodIds = $this->db->table(CatalogGood::getTableName())
                ->whereIn('group_id', $catalogGroupIds)
                ->pluck('id')->toArray();

            $newsIds = $this->db->table(NewsAdditional::getTableName())
                ->whereIn('relation_object_id', $goodIds)
                ->where('relation_object_type', NewsAdditionalTypeEnum::GOOD->value)
                ->pluck('news_id')->toArray();

            $query->whereIn('id', $newsIds);
        }

        $query->orderByRaw("COALESCE(published_at, created_at) $direction");

        $sql = $query->toSql();

        return $query->paginate($perPage, ['id'], 'page', $page);
    }

    public function getGoodsOrGroupsIds(int $newsId, ?NewsAdditionalTypeEnum $type): array
    {
        return $this->db->table(NewsAdditional::getTableName())
            ->where('news_id', $newsId)
            ->when($type, fn($query, $type) => $query->where('relation_object_type', $type->value))
            ->orderBy('sort')
            ->get(['id', 'relation_object_id', 'relation_object_type', 'sort'])
            ->all();
    }

    public function addGood(int $newsId, int $objectId, NewsAdditionalTypeEnum $type): void
    {
        $this->db->table(NewsAdditional::getTableName())
            ->updateOrInsert([
                'news_id'              => $newsId,
                'relation_object_type' => $type->value,
                'relation_object_id'   => $objectId
            ], [
                'created_at' => now()
            ]);
    }

    public function deleteObjectFromNews(int $id, int $newsId): void
    {
        NewsAdditional::where('id', $id)->delete();
    }

    public function removeAllGoods(int $newsId): void
    {
        $this->db->table(NewsAdditional::getTableName())
            ->where('news_id', $newsId)
            ->delete();
    }

    public function saveSubgroup(int $subgroupId, array $data): void
    {
        if ($subgroupId > 0) {
            $this->db->table(NewsSubgroup::getTableName())->where('id', $subgroupId)->update($data);
        } else {
            $this->db->table(NewsSubgroup::getTableName())->insert($data);
        }
    }

    public function deleteSubgroup(int $subgroupId): void
    {
        $this->db->table(NewsSubgroup::getTableName())->where('id', $subgroupId)->delete();
    }

    public function updateNewsAdditionalSort(int $id, int $newsId, int $sort): void
    {
        $this->db->table(NewsAdditional::getTableName())->where('id', $id)->update(['sort' => $sort]);
    }

    public function cloneNews(int $newsId): int
    {
        $news = News::loadBy($newsId);
        $newsData = $news->toArray();
        $newsData['created_at'] = now();
        $newsData['updated_at'] = null;
        $newsData['published_at'] = null;
        $newsData['active'] = false;
        $newsData['public'] = false;
        $newsData['title'] = $newsData['title'] . ' (копия)';
        $newsData['code'] = $newsData['code'] . '-copy';
        $newsData['language'] = $news->getLanguage()->value;
        $newsData['group_id'] = $news->getGroupId();
        unset($newsData['id']);

        $newNewsId = $this->db->table(News::getTableName())->insertGetId($newsData);

        $additionalData = $this->db->table(NewsAdditional::getTableName())->where('news_id', $newsId)->get()->all();

        foreach ($additionalData as &$additional) {
            $additional = (array)$additional;
            $additional['created_at'] = now();
            $additional['news_id'] = $newNewsId;
            unset($additional['id']);
        }

        $this->db->table(NewsAdditional::getTableName())->insert($additionalData);

        // news_in_subgroups
        $subgroups = $this->db->table(NewsInSubgroup::getTableName())->where('news_id', $newsId)->get()->all();
        $subgroupsData = [];
        foreach ($subgroups as $subgroup) {
            $subgroup = (array)$subgroup;
            $subgroup['news_id'] = $newNewsId;
            unset($subgroup['id']);
            $subgroupsData[] = $subgroup;
        }

        $this->db->table(NewsInSubgroup::getTableName())->insert($subgroupsData);

        return $newNewsId;
    }

    public function addNewsMedia(MediaType $mediaType, RelationMediaType $type, int $newsId, string $path): int
    {
        return $this->db->table(NewsMedia::getTableName())
            ->insertGetId([
                'type'       => $type->value,
                'path'       => $path,
                'news_id'    => $newsId,
                'media_type' => $mediaType->value,
            ]);
    }

    public function getNewsMedia(RelationMediaType $type, int $objectId): ?NewsMedia
    {
        return NewsMedia::where('type', $type->value)->where('news_id', $objectId)->first();
    }
}
