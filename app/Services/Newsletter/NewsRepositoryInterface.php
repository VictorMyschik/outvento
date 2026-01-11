<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Models\News\News;
use App\Services\Newsletter\Enum\NewsAdditionalTypeEnum;

interface NewsRepositoryInterface
{
    public function getGroupList(): array;

    public function saveNews(int $newsId, array $data): int;

    public function saveGroup(int $groupId, array $data): int;

    public function deleteNews(int $newsId): void;

    public function deleteGroup(int $groupId): void;

    public function getNewsById(int $newsId): ?News;

    public function getGoodsOrGroupsIds(int $newsId, ?NewsAdditionalTypeEnum $type): array;

    public function addGood(int $newsId, int $objectId, NewsAdditionalTypeEnum $type): void;

    public function deleteObjectFromNews(int $id, int $newsId): void;

    public function removeAllGoods(int $newsId): void;

    public function saveSubgroup(int $subgroupId, array $data): void;

    public function deleteSubgroup(int $subgroupId): void;

    public function updateNewsAdditionalSort(int $id, int $newsId, int $sort): void;

    public function getTodayNewsList(): array;
}
