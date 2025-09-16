<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries;

use App\Jobs\Catalog\Wildberries\WBUpdateAttributesJob;
use App\Jobs\Catalog\Wildberries\WBUpdateCatalogChildGroupsJob;
use App\Models\Catalog\Wildberries\WBCatalogGood;
use App\Models\Catalog\Wildberries\WBCatalogNotFound;
use App\Repositories\Catalog\Wildberries\WBGoodsInterface;
use App\Services\Catalog\Wildberries\API\WBClient;
use App\Services\Catalog\Wildberries\DTO\WBGoodDto;
use App\Services\Catalog\Wildberries\Enum\WBCatalogAttributeGroupEnum;
use Illuminate\Support\Facades\Cache;

final readonly class WBImportService
{
    private const string KEY = 'wb_system_tokens';

    public function __construct(
        private WBClient               $client,
        private WBGoodsInterface       $repository,
        private ImageUploaderInterface $uploader,
    ) {}

    #region GROUPS
    public function updateCatalogGroups(): void
    {
        $this->clearCachedTokens();

        $this->updateBaseGroups();
        $this->updateChildGroups();
    }

    public function updateBaseGroups(): void
    {
        $response = $this->client->getBaseGroups($this->getToken());

        $new = [];
        $list = $this->repository->getBaseGroups();

        foreach ($response->data ?? [] as $group) {
            if (!isset($list[$group->id])) {
                $new[] = $group;
            }
        }

        $this->repository->saveBaseGroups($new);
    }

    public function updateAttributes(int $groupId): void
    {
        $response = $this->client->getGroupAttributes($groupId, $this->getToken());

        foreach ($response->data ?? [] as $attribute) {
            $this->repository->saveReferenceAttribute($attribute);
            $this->repository->saveAttribute($groupId, $attribute, WBCatalogAttributeGroupEnum::CHARACTERISTIC);
        }
    }

    public function updateChildGroups(): void
    {
        $groups = $this->repository->getFullGroups();

        foreach ($groups as $group) {
            WBUpdateCatalogChildGroupsJob::dispatch($group->id);
        }
    }

    public function updateByGroup(int $groupId): void
    {
        try {
            $response = $this->client->getChildGroups($groupId, $this->getToken());

            foreach ($response->data ?? [] as $childGroup) {
                $this->repository->saveChildGroup($childGroup);
                WBUpdateCatalogChildGroupsJob::dispatch($childGroup->subjectID);
                // Update attributes
                WBUpdateAttributesJob::dispatch($childGroup->subjectID);
            }
        } catch (\Throwable $e) {
            WBUpdateCatalogChildGroupsJob::dispatch($groupId);
        }
    }

    private function getToken(): string
    {
        foreach ($this->getList() as $key => $value) {
            if ((int)time() > (int)$value) {
                $this->updateToken($key);

                return $key;
            }
        }

        sleep(1);

        return $this->getToken();
    }

    private function updateToken(string|int $token): void
    {
        $list = $this->getList();
        unset($list[$token]);
        $list[$token] = time();
        Cache::put(self::KEY, $list);
    }

    private function getList(): array
    {
        return Cache::get(self::KEY, array_flip(config('wildberries.content.tokens')));
    }

    private function clearCachedTokens(): void
    {
        Cache::forget(self::KEY);
    }
    #endregion

    #region GOODS
    public function loadGood(int $wbId): void
    {
        if (WBCatalogGood::where('nm_id', $wbId)->exists() || WBCatalogNotFound::where('wb_id', $wbId)->exists()) {
           return;
        }

        $url = $this->generateGoodUrl($wbId);

        try {
            $response = $this->client->getGood($url);
        } catch (\Exception $e) {
            WBCatalogNotFound::create(['wb_id' => $wbId]);

            return;
        }

        if (empty($response['media']) || empty($response['slug']) || empty($response['selling']['supplier_id'])) {
            WBCatalogNotFound::create(['wb_id' => $wbId]);

            return;
        }

        $groupId = $this->getGroupId($response);

        $goodDto = new WBGoodDto(
            nm_id: (int)$response['nm_id'],
            imt_id: (int)$response['imt_id'],
            subject_id: (int)$groupId,
            vendor_code: $response['vendor_code'] ?? null,
            brand_id: $this->repository->getOrCreate($response['selling']),
            title: $response['imt_name'],
            description: $response['description'] ?? null,
            sl: json_encode($response),
        );

        $newGoodId = $this->repository->saveGood(0, $goodDto);

        for ($i = 0; $i < (int)($response['media']['photo_count'] ?? 0); $i++) {
            $imageUrl = $this->generateGoodImageUrl($wbId, $i + 1);
            $this->uploader->uploadImageByURL($newGoodId, $imageUrl);
        }
    }

    public function reloadGoods(WBCatalogGood $good): void
    {
        $count = $good->getJsonField('media')['photo_count'] ?? 0;

        if (!$count) {
            return;
        }

        for ($i = 0; $i < (int)$count; $i++) {
            $imageUrl = $this->generateGoodImageUrl($good->getNmId(), $i + 1);
            $this->uploader->uploadImageByURL($good->id(), $imageUrl);
        }
    }

    private function getGroupId(array $response): int
    {
        $group = $this->repository->getGroupById((int)$response['data']['subject_id']);
        if (!$group) {
            $this->updateByGroup((int)$response['data']['subject_root_id']);
            $group = $this->repository->getGroupById((int)$response['data']['subject_id']);
        }

        if (!$group) {
            throw new \Exception('Group not found ID' . (int)$response['data']['subject_id']);
        }

        return $group->id();
    }

    private function generateGoodUrl(int $wbId): string
    {
        [$basket, $vol, $part, $wbId] = $this->calculateUrlElements($wbId);

        return sprintf('https://basket-%s.wbbasket.ru/vol%s/part%s/%s/info/ru/card.json', $basket, $vol, $part, $wbId);
    }

    private function generateGoodImageUrl(int $wbId, int $imgNumber): string
    {
        [$basket, $vol, $part, $wbId] = $this->calculateUrlElements($wbId);

        return sprintf('https://basket-%s.wbbasket.ru/vol%s/part%s/%s/images/big/%s.webp', $basket, $vol, $part, $wbId, $imgNumber);
    }

    private function calculateUrlElements(int $wbId): array
    {
        $part = substr((string)$wbId, 0, -3);
        $vol = substr((string)$wbId, 0, -5);

        $basket = match (true) {
            $vol >= 0 && $vol <= 143 => "01",
            $vol <= 287 => "02",
            $vol <= 431 => "03",
            $vol <= 719 => "04",
            $vol <= 1007 => "05",
            $vol <= 1061 => "06",
            $vol <= 1115 => "07",
            $vol <= 1169 => "08",
            $vol <= 1313 => "09",
            $vol <= 1601 => "10",
            $vol <= 1655 => "11",
            $vol <= 1919 => "12",
            $vol <= 2045 => "13",
            $vol <= 2189 => "14",
            $vol <= 2405 => "15",
            $vol <= 2621 => "16",
            $vol <= 2837 => "17",
            $vol <= 3053 => "18",
            $vol <= 3269 => "19",
            $vol <= 3485 => "20",
            $vol <= 3701 => "21",
            $vol <= 3917 => "22",
            $vol <= 4133 => "23",
            $vol <= 4349 => "24",
            $vol <= 4565 => "25",
            $vol <= 4781 => "26",
            $vol <= 4997 => "27",
            $vol <= 5213 => "28",
            $vol <= 5429 => "29",
        };

        return [$basket, $vol, $part, $wbId];
    }
    #endregion
}
