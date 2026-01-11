<?php

declare(strict_types=1);

namespace App\Services\Constructor;

use App\Models\Constructor\Constructor;
use App\Models\Constructor\ConstructorItemOutVideo;
use App\Models\Constructor\ConstructorItemSlide;
use App\Models\Constructor\ConstructorItemText;
use App\Models\News\NewsMedia;
use App\Orchid\Enums\ConstructorObjectTypeEnum;
use App\Repositories\Constructor\ConstructorFileStorage;
use App\Services\Constructor\DTO\SlideDTO;
use App\Services\Constructor\Enum\ConstructorFileType;
use App\Services\Constructor\Enum\ConstructorTypeEnum;
use Exception;
use Illuminate\Http\UploadedFile;

final readonly class ConstructorService
{
    public function __construct(
        private ConstructorRepositoryInterface $repository,
        private ConstructorFileStorage         $storage,
    ) {}

    public function getBlockById(int $id): ?Constructor
    {
        return $this->repository->getConstructorById($id);
    }

    public function saveConstructorBlock(int $id, array $input): int
    {
        return $this->repository->saveConstructorBlock($id, $input);
    }

    public function deleteAllConstructorBlocks(int $objectId, ConstructorObjectTypeEnum $type): void
    {
        foreach ($this->getConstructorBlocks($objectId, $type) as $item) {
            $this->deleteBlockAllItem($objectId, $item->id);
        }

        $this->repository->deleteAllConstructorBlocks($objectId, $type);
    }

    public function deleteConstructorBlock(int $objectId, int $blockId): void
    {
        $this->deleteBlockAllItem($objectId, $blockId);
        $this->repository->deleteConstructorBlocks($objectId, $blockId);
    }

    public function getConstructorBlocks(int $objectId, ConstructorObjectTypeEnum $type): array
    {
        return $this->repository->getConstructorBlocks($objectId, $type);
    }

    public function getBlockItemById(int $itemId, ConstructorTypeEnum $type): mixed
    {
        return match ($type) {
            ConstructorTypeEnum::Text => $this->repository->getBlockItemText($itemId),
            ConstructorTypeEnum::Slider => $this->repository->getBlockItemSlider($itemId),
            ConstructorTypeEnum::Video => $this->repository->getBlockItemVideo($itemId),
            ConstructorTypeEnum::OutVideo => $this->repository->getBlockItemOutVideo($itemId),
        };
    }

    public function saveBlockItemText(int $itemId, array $input): int
    {
        return $this->repository->saveBlockItemText($itemId, $input);
    }

    public function saveBlockItemVideo(int $itemId, array $input): int
    {
        if (!empty($input['file'])) {
            $input['file_id'] = $this->saveFile(
                constructorId: $input['constructor_id'],
                itemId: $itemId,
                file: $input['file'],
                type: ConstructorFileType::Video
            );

            unset($input['file']);
        }

        return $this->repository->saveBlockItemVideo($itemId, $input);
    }

    public function saveBlockItemOutVideo(int $itemId, array $input): int
    {
        return $this->repository->saveBlockItemOutVideo($itemId, $input);
    }

    public function getBlockItems(int $blockId): array
    {
        return $this->repository->getBlockItems($blockId);
    }

    public function deleteBlockAllItem(int $objectId, int $blockId): void
    {
        foreach ($this->getBlockItems($blockId) as $item) {
            $type = ConstructorTypeEnum::from($item->type);
            $this->deleteBlockItem($item->id, $type, $objectId);
        }
    }

    public function deleteBlockItem(int $itemId, ConstructorTypeEnum $type, int $objectId): void
    {
        match ($type) {
            ConstructorTypeEnum::Text => $this->repository->deleteBlockItemText($itemId, $objectId),
            ConstructorTypeEnum::Slider => $this->deleteSlider($itemId, $objectId),
            ConstructorTypeEnum::Video => $this->deleteBlockItemVideo($itemId),
            ConstructorTypeEnum::OutVideo => $this->repository->deleteBlockItemOutVideo($itemId, $objectId),
        };
    }

    private function deleteSlider(int $itemId): void
    {
        $slider = $this->getBlockItemById($itemId, ConstructorTypeEnum::Slider);

        foreach ($slider->images as $slide) {
            $this->storage->deleteFile($slide->path);
        }

        $this->repository->deleteBlockItemSlider($itemId);
    }

    private function deleteBlockItemVideo(int $itemId): void
    {
        try {
            $item = $this->repository->getBlockItemVideo($itemId);
            $this->storage->deleteFileById($item->getFile());
            $this->repository->deleteBlockItemVideo($itemId);
        } catch (Exception $exception) {
            // do nothing
        }
    }

    public function saveSlider(int $sliderId, array $input): int
    {
        return $this->repository->saveSlider($sliderId, $input);
    }

    public function getBlockItemSlideById(int $slideId): ?ConstructorItemSlide
    {
        return $this->repository->getBlockItemSlideById($slideId);
    }

    public function saveBlockItemSlide(SlideDTO $dto): int
    {
        $data = [
            'slider_id'    => $dto->sliderId,
            'display_name' => $dto->displayName,
            'alt'          => $dto->alt,
            'sort'         => $dto->sort,
        ];

        if ($dto->image) {
            $data['path'] = $this->storage->saveFile(constructorId: $dto->constructorId, itemId: $dto->slideId, file: $dto->image);
        }

        return $this->repository->saveBlockItemSlide($dto->slideId, $data);
    }

    public function deleteBlockItemSlide(int $slide_id, int $objectId): void
    {
        $slide = $this->getBlockItemSlideById($slide_id);
        $this->storage->deleteFile($slide->path);
        $this->repository->deleteBlockItemSlide($slide_id, $objectId);
    }

    public function saveFile(int $constructorId, int $itemId, UploadedFile $file, ConstructorFileType $type): int
    {
        $path = $this->storage->saveFile(constructorId: $constructorId, itemId: $itemId, file: $file);

        return $this->repository->createConstructorFileModel([
            'constructor_id' => $constructorId,
            'type'           => $type->value,
            'path'           => $path,
            'file_name'      => $file->getClientOriginalName(),
            'size'           => $file->getSize(),
            'extension'      => $file->extension(),
        ]);
    }

    public function getBlockIcon(int $id): ?NewsMedia
    {
        return $this->repository->getBlockIcon($id);
    }

    public function cloneConstructorBlocks(ConstructorObjectTypeEnum $type, int $oldId, int $newId): void
    {
        foreach ($this->getConstructorBlocks($oldId, $type) as $constructorBlock) {
            $input = $constructorBlock->toArray();
            $input['object_id'] = $newId;
            unset($input['id']);
            $newConstructorBlockId = $this->repository->saveConstructorBlock(0, $input);

            foreach ($this->getBlockItems($constructorBlock->id()) as $blockItem) {
                match ($blockItem->type) {
                    ConstructorTypeEnum::Text->value => $this->cloneBlockItemText($blockItem, $newConstructorBlockId),
                    ConstructorTypeEnum::OutVideo->value => $this->cloneBlockItemOutVideo($blockItem, $newConstructorBlockId),
                    default => throw new Exception('Unknown block item type for cloning: ' . $blockItem->type),
                };
            }
        }
    }

    private function cloneBlockItemText(object $block, int $constrictorBlockId): void
    {
        /** @var ConstructorItemText $text */
        $text = $this->getBlockItemById($block->id, ConstructorTypeEnum::Text);
        $data = $text->toArray();
        $data['constructor_id'] = $constrictorBlockId;
        unset($data['id']);

        $this->saveBlockItemText(0, $data);
    }

    private function cloneBlockItemOutVideo(object $block, int $constrictorBlockId): void
    {
        /** @var ConstructorItemOutVideo $outVideo */
        $outVideo = $this->getBlockItemById($block->id, ConstructorTypeEnum::OutVideo);
        $data = $outVideo->toArray();
        $data['constructor_id'] = $constrictorBlockId;
        unset($data['id']);

        $this->saveBlockItemOutVideo(0, $data);
    }
}