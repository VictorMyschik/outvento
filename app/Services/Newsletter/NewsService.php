<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Models\News\News;
use App\Models\News\NewsGroup;
use App\Models\News\NewsMedia;
use App\Models\User;
use App\Services\Constructor\ConstructorService;
use App\Services\Newsletter\Enum\MediaType;
use App\Services\Newsletter\Enum\NewsAdditionalTypeEnum;
use App\Services\Newsletter\Enum\RelationMediaType;
use App\Services\Newsletter\ImageUploader\NewsMediaUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

final readonly class NewsService
{
    public function __construct(
        private NewsRepositoryInterface $repository,
        private NewsMediaUploader       $imageUploader,
        private ConstructorService      $constructorService,
    ) {}

    #region Groups
    public function getGroupList(): array
    {
        return $this->repository->getGroupList();
    }

    public function getGroupSelectList(): array
    {
        $list = [];
        foreach ($this->repository->getGroupList() as $group) {
            $list[$group->id] = $group->title;
        }

        return $list;
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
        $this->imageUploader->deleteMedia(RelationMediaType::NewsLogo, $newsId);
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
        $this->imageUploader->deleteMedia(RelationMediaType::NewsLogo, $newsId);
        $this->imageUploader->uploadMedia($image, $newsId, RelationMediaType::NewsLogo);
    }

    public function deleteNews(int $newsId): void
    {
        $this->repository->deleteNews($newsId);
    }

    public function getLogo(int $newsId): ?NewsMedia
    {
        return $this->imageUploader->get(Type::News, $newsId);
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

    public function cloneNews(int $newsId): int
    {
        $news = $this->getNewsById($newsId);
        $newNewsId = $this->repository->cloneNews($newsId);
        $logo = $this->getLogo($newsId);
        $logo && $this->saveLogo($newNewsId, new UploadedFile($logo->getFullPath(), $logo->getName(), $logo->getMime(), null, true));

        $this->constructorService->cloneConstructorBlocks(
            ConstructorObjectTypeEnum::NEWS,
            $newsId,
            $newNewsId,
            $news->getLanguage(),
            User::find(Auth::id())
        );

        return $newNewsId;
    }
}
