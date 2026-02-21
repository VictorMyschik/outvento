<?php

declare(strict_types=1);

namespace App\Services\References;

use App\Models\Travel\Activity;
use App\Services\References\Enum\ImageTypeEnum;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class TravelTypeService extends AbstractReferenceService
{
    public function saveTravelType(int $id, array $data, ?UploadedFile $file): int
    {
        if ($file) {
            $data['image_path'] = $this->saveImage(ImageTypeEnum::TravelType, $file);
        }

        return $this->repository->save($id, Activity::class, $data);
    }
}