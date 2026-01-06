<?php

declare(strict_types=1);

namespace App\Repositories\Constructor;

use App\Models\Constructor\Constructor;
use App\Models\Constructor\ConstructorFile;
use App\Models\Constructor\ConstructorItemOutVideo;
use App\Models\Constructor\ConstructorItemSlide;
use App\Models\Constructor\ConstructorItemSlider;
use App\Models\Constructor\ConstructorItemText;
use App\Models\Constructor\ConstructorItemVideo;
use App\Models\News\NewsMedia;
use App\Orchid\Enums\ConstructorObjectTypeEnum;
use App\Repositories\DatabaseRepository;
use App\Services\Constructor\ConstructorRepositoryInterface;
use App\Services\Constructor\Enum\ConstructorItemType;

final readonly class ConstructorRepository extends DatabaseRepository implements ConstructorRepositoryInterface
{
    public function getConstructorById(int $constructorId): ?Constructor
    {
        return Constructor::loadBy($constructorId);
    }

    public function saveConstructorBlock(int $blockId, array $data): int
    {
        if ($blockId > 0) {
            $this->db->table(Constructor::getTableName())->where('id', $blockId)->update($data);
            return $blockId;
        }

        return $this->db->table(Constructor::getTableName())->insertGetId($data);
    }

    public function deleteAllConstructorBlocks(int $objectId, ConstructorObjectTypeEnum $type): void
    {
        $this->db->table(Constructor::getTableName())->where('object_id', $objectId)->where('object_type', $type->value)->delete();
    }

    public function deleteConstructorBlocks(int $objectId, int $functionId): void
    {
        $this->db->table(Constructor::getTableName())->where('id', $functionId)->delete();
    }

    public function getConstructorBlocks(int $objectId, ConstructorObjectTypeEnum $type): array
    {
        return Constructor::where('object_id', $objectId)->where('object_type', $type->value)->orderBy('sort')->get()->all();
    }

    public function getBlockItemText(int $itemId): ?ConstructorItemText
    {
        return ConstructorItemText::loadBy($itemId);
    }

    public function getBlockItemVideo(int $itemId): ?ConstructorItemVideo
    {
        return ConstructorItemVideo::join(
            ConstructorFile::getTableName(),
            ConstructorItemVideo::getTableName() . '.file_id',
            '=',
            ConstructorFile::getTableName() . '.id'
        )
            ->where(ConstructorItemVideo::getTableName() . '.id', $itemId)->get([
                ConstructorItemVideo::getTableName() . '.*',
                ConstructorFile::getTableName() . '.path',
                ConstructorFile::getTableName() . '.file_name',
                ConstructorFile::getTableName() . '.path',
                ConstructorFile::getTableName() . '.size',
                ConstructorFile::getTableName() . '.extension',
            ])->first();
    }

    public function getBlockItemOutVideo(int $itemId): ?ConstructorItemOutVideo
    {
        return ConstructorItemOutVideo::loadBy($itemId);
    }

    public function getBlockItemSlider(int $itemId): ?ConstructorItemSlider
    {
        $slider = ConstructorItemSlider::loadBy($itemId);

        if (is_null($slider)) {
            return null;
        }

        $slider->images = ConstructorItemSlide::where('slider_id', $itemId)->orderBy('sort')->get()->all();

        return $slider;
    }

    public function saveBlockItemText(int $itemId, array $data): int
    {
        if ($itemId > 0) {
            $this->db->table(ConstructorItemText::getTableName())->where('id', $itemId)->update($data);
            return $itemId;
        }

        return $this->db->table(ConstructorItemText::getTableName())->insertGetId($data);
    }

    public function saveBlockItemVideo(int $itemId, array $data): int
    {
        if ($itemId > 0) {
            $this->db->table(ConstructorItemVideo::getTableName())->where('id', $itemId)->update($data);
            return $itemId;
        }

        return $this->db->table(ConstructorItemVideo::getTableName())->insertGetId($data);
    }

    public function saveBlockItemOutVideo(int $itemId, array $data): int
    {
        if ($itemId > 0) {
            $this->db->table(ConstructorItemOutVideo::getTableName())->where('id', $itemId)->update($data);
            return $itemId;
        }

        return $this->db->table(ConstructorItemOutVideo::getTableName())->insertGetId($data);
    }

    public function getBlockItems(int $blockId): array
    {
        $text = $this->db->table(ConstructorItemText::getTableName())
            ->where('constructor_id', $blockId)->selectRaw('id as id, sort, \'text\' as "type"');

        $slider = $this->db->table(ConstructorItemSlider::getTableName())
            ->where('constructor_id', $blockId)->selectRaw('id as id, sort, \'slider\' as "type"');

        $video = $this->db->table(ConstructorItemVideo::getTableName())
            ->where('constructor_id', $blockId)->selectRaw('id as id, sort, \'video\' as "type"');

        $outVideo = $this->db->table(ConstructorItemOutVideo::getTableName())
            ->where('constructor_id', $blockId)->selectRaw('id as id, sort, \'out_video\' as "type"');

        $union = $text->union($slider)->union($video)->union($outVideo)->orderBy('sort')->get();

        return $union->all();
    }

    public function deleteBlockItemText(int $itemId): void
    {
        $this->db->table(ConstructorItemText::getTableName())->where('id', $itemId)->delete();
    }

    public function deleteBlockItemOutVideo(int $itemId): void
    {
        $this->db->table(ConstructorItemOutVideo::getTableName())->where('id', $itemId)->delete();
    }

    public function deleteBlockItemSlider(int $itemId): void
    {
        $this->db->table(ConstructorItemSlider::getTableName())->where('id', $itemId)->delete();
    }

    public function deleteBlockItemVideo(int $itemId): void
    {
        $this->db->table(ConstructorItemVideo::getTableName())->where('id', $itemId)->delete();
    }

    public function saveSlider(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(ConstructorItemSlider::getTableName())->where('id', $id)->update($data);
            return $id;
        }

        return $this->db->table(ConstructorItemSlider::getTableName())->insertGetId($data);
    }

    public function getBlockItemSlideById(int $slideId): ?ConstructorItemSlide
    {
        return ConstructorItemSlide::loadBy($slideId);
    }

    public function saveBlockItemSlide(int $slideId, array $data): int
    {
        if ($slideId > 0) {
            $this->db->table(ConstructorItemSlide::getTableName())->where('id', $slideId)->update($data);
            return $slideId;
        }

        return $this->db->table(ConstructorItemSlide::getTableName())->insertGetId($data);
    }

    public function deleteBlockItemSlide(int $slideId, int $objectId): void
    {
        $this->db->table(ConstructorItemSlide::getTableName())->where('id', $slideId)->delete();
    }

    public function getBlockIcon(int $id): ?NewsMedia
    {
        return NewsMedia::where('type', ConstructorItemType::ConstructorBlockIcon->value)->where('id', $id)->first();
    }

    public function createConstructorFileModel(array $data): int
    {
        return $this->db->table(ConstructorFile::getTableName())->insertGetId($data);
    }
}