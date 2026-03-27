<?php

declare(strict_types=1);

namespace App\Services\References;

use App\Models\Notification\NotificationEventType;
use App\Models\UserInfo\CommunicationType;
use App\Services\References\Enum\ImageTypeEnum;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class CommunicateTypeService extends AbstractReferenceService
{
    public function saveCommunicationType(int $id, array $data, ?UploadedFile $file): int
    {
        if ($file) {
            $data['image_path'] = $this->saveImage(ImageTypeEnum::CommunicationType, $file);
        }

        return $this->repository->save($id, CommunicationType::class, $data);
    }
}