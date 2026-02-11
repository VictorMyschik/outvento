<?php

declare(strict_types=1);

namespace App\Services\References;

use App\Models\Notification\NotificationEventType;
use App\Services\References\Enum\ImageTypeEnum;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class NotificationEventTypeService extends AbstractReferenceService
{
    public function saveNotificationType(int $id, array $data, ?UploadedFile $file): int
    {
        if ($file) {
            $data['image_path'] = $this->saveImage(ImageTypeEnum::NotificationEventType, $file);
        }

        return $this->repository->save($id, NotificationEventType::class, $data);
    }
}