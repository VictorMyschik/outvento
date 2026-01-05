<?php

declare(strict_types=1);

namespace App\Services\Constructor;

use App\Models\Constructor\Constructor;
use App\Models\Constructor\ConstructorItemOutVideo;
use App\Models\Constructor\ConstructorItemSlide;
use App\Models\Constructor\ConstructorItemSlider;
use App\Models\Constructor\ConstructorItemText;
use App\Models\Constructor\ConstructorItemVideo;
use App\Models\News\NewsMedia;
use App\Orchid\Enums\ConstructorObjectTypeEnum;
use App\Services\System\Enum\Language;

interface ConstructorRepositoryInterface
{
    public function getBlockById(int $blockId): ?Constructor;

    public function saveConstructorBlock(int $blockId, array $data): int;

    public function deleteAllConstructorBlocks(int $objectId, ConstructorObjectTypeEnum $type, Language $language): void;

    public function deleteConstructorBlocks(int $objectId, int $functionId): void;

    public function getConstructorBlocks(int $objectId, ConstructorObjectTypeEnum $type, Language $language): array;

    public function getBlockItemText(int $itemId): ?ConstructorItemText;

    public function getBlockItemVideo(int $itemId): ?ConstructorItemVideo;

    public function getBlockItemOutVideo(int $itemId): ?ConstructorItemOutVideo;

    public function getBlockItemSlider(int $itemId): ?ConstructorItemSlider;

    public function saveBlockItemText(int $itemId, array $data): int;

    public function saveBlockItemVideo(int $itemId, array $data): int;

    public function saveBlockItemOutVideo(int $itemId, array $data): int;

    public function getBlockItems(int $blockId): array;

    public function deleteBlockItemText(int $itemId, int $objectId): void;

    public function deleteBlockItemOutVideo(int $itemId, int $objectId): void;

    public function deleteBlockItemSlider(int $itemId, int $objectId): void;

    public function deleteBlockItemVideo(int $itemId, int $objectId): void;

    public function saveSlider(int $id, array $data): int;

    public function getBlockItemSlideById(int $slideId): ?ConstructorItemSlide;

    public function saveBlockItemSlide(int $slideId, array $data): int;

    public function deleteBlockItemSlide(int $slideId, int $objectId): void;

    public function getBlockIcon(int $id): ?NewsMedia;
}