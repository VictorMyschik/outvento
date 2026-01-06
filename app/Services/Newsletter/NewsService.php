<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Models\News\News;
use App\Models\News\NewsGroup;
use App\Models\News\NewsMedia;
use App\Services\Newsletter\Enum\NewsAdditionalTypeEnum;
use App\Services\Newsletter\ImageUploader\Enum\NewsFileType;
use App\Services\Newsletter\ImageUploader\Enum\NewsMediaType;
use App\Services\Newsletter\ImageUploader\NewsMediaUploader;
use Illuminate\Http\UploadedFile;

final readonly class NewsService
{
    public function __construct(
        private NewsRepositoryInterface $repository,
        private NewsMediaUploader       $mediaUploader,
    ) {}

    #region Groups
    public function getGroupList(): array
    {
        return $this->repository->getGroupList();
    }

    public function getGroupById(int $groupId): ?NewsGroup
    {
        return $this->getGroupList()[$groupId] ?? null;
    }

    public function saveGroup(int $groupId, array $data): int
    {
        return $this->repository->saveGroup($groupId, $data);
    }

    public function deleteGroup(int $groupId): void
    {
        $this->repository->deleteGroup($groupId);
    }
    #endregion

    #region Logo
    public function removeLogo(int $newsId): void
    {
        $this->mediaUploader->deleteMedia(NewsMediaType::Logo, $newsId);
    }
    #endregion

    #region News
    public function getNewsById(int $newsId): ?News
    {
        return $this->repository->getNewsById($newsId);
    }

    public function saveNews(int $newsId, array $data): int
    {
        if (!empty($data['logo'])) {
            $this->saveLogo($newsId, $data['logo']);
            unset($data['logo']);
        }

        return $this->repository->saveNews($newsId, $data);
    }

    private function saveLogo(int $newsId, UploadedFile $image): void
    {
        $this->mediaUploader->deleteMedia(NewsMediaType::Logo, $newsId);
        $this->mediaUploader->uploadMedia($image, $newsId, NewsMediaType::Logo, NewsFileType::Image);
    }

    public function deleteNews(int $newsId): void
    {
        $this->repository->deleteNews($newsId);
    }

    public function getLogo(int $newsId): ?NewsMedia
    {
        return $this->repository->getNewsMedia(NewsMediaType::Logo, $newsId);
    }
    #endregion

    #region Goods
    public function getGoodsOrGroupsIds(int $newsId, ?NewsAdditionalTypeEnum $type): array
    {
        return $this->repository->getGoodsOrGroupsIds($newsId, $type);
    }

    public function addGoodOrGroup(int $newsId, int $goodId, NewsAdditionalTypeEnum $type): void
    {
        $this->repository->addGood($newsId, $goodId, $type);
    }

    public function deleteObjectFromNews(int $id, int $newsId): void
    {
        $this->repository->deleteObjectFromNews($id, $newsId);
    }

    public function removeAllGoods(int $newsId): void
    {
        $this->repository->removeAllGoods($newsId);
    }

    #endregion

    public function saveSubgroup(int $subgroupId, array $data): void
    {
        $this->repository->saveSubgroup($subgroupId, $data);
    }

    public function deleteSubgroup(int $subgroupId): void
    {
        $this->repository->deleteSubgroup($subgroupId);
    }

    public function updateNewsAdditionalSort(int $id, int $newsId, int $sort): void
    {
        $this->repository->updateNewsAdditionalSort($id, $newsId, $sort);
    }
}
