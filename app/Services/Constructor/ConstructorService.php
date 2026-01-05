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
use App\Models\Orchid\Attachment;
use App\Models\User;
use App\Orchid\Enums\ConstructorObjectTypeEnum;
use App\Repositories\Constructor\Storage\Enum\StorageFileTypeEnum;
use App\Repositories\Constructor\Storage\FileStorage;
use App\Services\Constructor\Enum\ConstructorTypeEnum;
use App\Services\Newsletter\Enum\RelationMediaType;
use App\Services\Newsletter\ImageUploader\NewsMediaUploader;
use App\Services\System\Enum\Language;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final readonly class ConstructorService
{
    public function __construct(
        private ConstructorRepositoryInterface $repository,
        private FileStorage                    $storage,
        private NewsMediaUploader              $imageUploader
    ) {}

    public function getBlockById(int $id): ?Constructor
    {
        return $this->repository->getBlockById($id);
    }

    public function saveConstructorBlock(int $id, array $input): int
    {
        $icon = $input['icon'] ?? null;
        unset($input['icon']);

        $id = $this->repository->saveConstructorBlock($id, $input);

        if ($icon && $id) {
            $attachment = Attachment::loadByOrDie((int)$icon);
            $path = Storage::path($attachment->getFullPath());
            if (!file_exists($path) || !is_file($path)) {
                Attachment::where('hash', $attachment->getHash())->delete();
                throw new Exception('Ошибка при загрузке файла. Попробуйте ещё раз.');
            }

            $this->imageUploader->deleteMedia(RelationMediaType::ConstructorBlockIcon, $id);
            $this->imageUploader->uploadMedia(
                image: new UploadedFile($path, $attachment->getOriginalName(), $attachment->getMime(), null, true),
                newsId: $id,
                type: RelationMediaType::ConstructorBlockIcon,
            );
        }

        if ($attachment ?? null) {
            $attachment->deleteMr();
        }

        return $id;
    }

    public function deleteAllConstructorBlocks(int $objectId, ConstructorObjectTypeEnum $type, Language $language): void
    {
        foreach ($this->getConstructorBlocks($objectId, $type, $language) as $item) {
            $this->deleteBlockAllItem($objectId, $item->id);
        }

        $this->repository->deleteAllConstructorBlocks($objectId, $type, $language);
    }

    public function deleteConstructorBlock(int $objectId, int $blockId): void
    {
        $this->deleteBlockAllItem($objectId, $blockId);
        $this->repository->deleteConstructorBlocks($objectId, $blockId);
    }

    public function getConstructorBlocks(int $objectId, ConstructorObjectTypeEnum $type, Language $language): array
    {
        return $this->repository->getConstructorBlocks($objectId, $type, $language);
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
            ConstructorTypeEnum::Video => $this->deleteBlockItemVideo($itemId, $objectId),
            ConstructorTypeEnum::OutVideo => $this->repository->deleteBlockItemOutVideo($itemId, $objectId),
        };
    }

    private function deleteSlider(int $itemId, int $objectId): void
    {
        $slider = $this->getBlockItemById($itemId, ConstructorTypeEnum::Slider);

        foreach ($slider->images as $slide) {
            $this->storage->deleteFileById((int)$slide->id(), StorageFileTypeEnum::SlideImage);
        }

        $this->repository->deleteBlockItemSlider($itemId, $objectId);
    }

    private function deleteBlockItemVideo(int $itemId, int $objectId): void
    {
        try {
            $item = $this->repository->getBlockItemVideo($itemId);
            $this->storage->deleteFileById((int)$item->file_id, StorageFileTypeEnum::Video);
            $this->repository->deleteBlockItemVideo($itemId, $objectId);
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

    public function saveBlockItemSlide(int $slide_id, array $data): int
    {
        return $this->repository->saveBlockItemSlide($slide_id, $data);
    }

    public function deleteBlockItemSlide(int $slide_id, int $objectId): void
    {
        $this->repository->deleteBlockItemSlide($slide_id, $objectId);
    }

    public function saveFiles(int $sliderId, int $objectId, StorageFileTypeEnum $type, array $dtos): void
    {
        //TODO: Разобраться с конструктором
        $baseDir = config('storage.images');

        foreach ($dtos as $dto) {
            $this->storage->saveFile($dto->image, $type, $baseDir, [
                'slider_id'    => $sliderId,
                'display_name' => $dto->display_name,
                'sort'         => $dto->sort,
                'alt'          => $dto->alt,
            ]);
        }
    }

    public function saveFile(UploadedFile $file, StorageFileTypeEnum $type): int
    {
        return $this->storage->saveFile($file, $type);
    }

    public function getBlockIcon(int $id): ?NewsMedia
    {
        return $this->repository->getBlockIcon($id);
    }

    public function deleteBlockIcon(int $blockId): void
    {
        $this->imageUploader->deleteMedia(RelationMediaType::ConstructorBlockIcon, $blockId);
    }

    public function cloneConstructorBlocks(ConstructorObjectTypeEnum $type, int $oldId, int $newId, Language $language): void
    {
        foreach ($this->getConstructorBlocks($oldId, $type, $language) as $constructorBlock) {
            $input = $constructorBlock->toArray();
            $input['object_id'] = $newId;
            unset($input['id']);
            $newConstructorBlockId = $this->repository->saveConstructorBlock(0, $input);

            foreach ($this->getBlockItems($constructorBlock->id()) as $blockItem) {
                match ($blockItem->type) {
                    ConstructorTypeEnum::Text->value => $this->cloneBlockItemText($blockItem, $newConstructorBlockId),
                    ConstructorTypeEnum::Video->value => $this->cloneBlockItemVideo($blockItem, $newConstructorBlockId),
                    ConstructorTypeEnum::OutVideo->value => $this->cloneBlockItemOutVideo($blockItem, $newConstructorBlockId),
                    ConstructorTypeEnum::Slider->value => $this->cloneSlider($blockItem, $newConstructorBlockId),
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

    private function cloneBlockItemVideo(object $block, int $constrictorBlockId): void
    {
        /** @var ConstructorItemVideo $video */
        $video = $this->getBlockItemById($block->id, ConstructorTypeEnum::Video);
        $file = $this->storage->getFileByID($video->getFileId(), StorageFileTypeEnum::Video);

        $fileId = $this->saveFile(
            new UploadedFile(
                Storage::path($file->getFilePathWithName()),
                $file->getFileName(),
                $file->getExtension(),
                null,
                true
            ),
            StorageFileTypeEnum::Video,
        );

        $data = $video->toArray();
        $newItemVideo['constructor_id'] = $constrictorBlockId;
        $newItemVideo['file_id'] = $fileId;
        $newItemVideo['title'] = $data['title'];
        $newItemVideo['description'] = $data['description'];
        $newItemVideo['sort'] = $data['sort'];

        $this->saveBlockItemVideo(0, $newItemVideo);
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

    private function cloneSlider(object $block, int $constrictorBlockId): void
    {
        /** @var ConstructorItemSlider $slider */
        $slider = $this->getBlockItemById($block->id, ConstructorTypeEnum::Slider);
        $images = $slider->images;
        unset($slider->images);
        $data = $slider->toArray();
        $data['constructor_id'] = $constrictorBlockId;
        unset($data['id']);

        $newSliderId = $this->saveSlider(0, $data);

        /** @var  ConstructorItemSlide $slide */
        foreach ($images as $slide) {
            $file = new UploadedFile(
                Storage::path($slide->getFilePathWithName()),
                $slide->getFileName(),
                $slide->getMime(),
                null,
                true
            );

            $slideData = [
                'slider_id'    => $newSliderId,
                'display_name' => $slide->getDisplayName(),
                'sort'         => $slide->getSort(),
                'alt'          => $slide->getAlt(),
            ];

            $this->storage->saveFile($file, StorageFileTypeEnum::SlideImage, $slideData);
        }
    }
}