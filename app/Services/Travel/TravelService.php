<?php

declare(strict_types=1);

namespace App\Services\Travel;

use App\Models\Orchid\Attachment;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelImage;
use App\Models\User;
use App\Services\Travel\Enum\ImageType;
use App\Services\Travel\Enum\TravelStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

readonly class TravelService
{
    public function __construct(
        private TravelRepositoryInterface $travelRepository,
        private TravelUploadService       $fileStorage,
    ) {}

    public function getTravelById(int $travelId): ?Travel
    {
        return $this->travelRepository->getTravelById($travelId);
    }

    public function createTravel(array $data): int
    {
        $data['public_id'] = crc32((string)microtime());
        $data['status'] = TravelStatus::Draft;

        return $this->travelRepository->saveTravel(0, $data);
    }

    public function updateTravel(int $id, array $data): int
    {
        return $this->travelRepository->saveTravel($id, $data);
    }

    public function getPublicUrl(Travel $travel): string
    {
        return route('travel.public.link', ['token' => $travel->getPublicId()]);
    }

    public function getTravelUsers(Travel $travel): array
    {
        return $this->travelRepository->getTravelUsers($travel);
    }

    public function saveTravelImage(Travel $travel, Attachment $attachment, ImageType $type): int
    {
        $path = Storage::path($attachment->getFullPath());

        if (!file_exists($path) || !is_file($path)) {
            Attachment::where('hash', $attachment->getHash())->delete();
            throw new \Exception('Ошибка при загрузке файла. Попробуйте ещё раз.');
        }

        $uploadedFile = new UploadedFile($path, $attachment->getOriginalName(), $attachment->getMime(), null, true);

        $imageId = $this->fileStorage->uploadTravelImage($uploadedFile, $travel, $type);

        $attachment->delete();

        return $imageId;
    }

    public function deleteImage(int $imageId): void
    {
        $this->fileStorage->deleteFile(
            $this->travelRepository->getTravelImage($imageId)->getPath()
        );

        $this->travelRepository->deleteTravelImage($imageId);
    }

    public function deleteTravelImages(int $travelId): void
    {
        $logo = $this->getTravelLogo($travelId);
        if ($logo !== null) {
            $this->deleteImage($logo->id());
        }

        foreach ($this->getTravelPhotoList($travelId) as $image) {
            $this->deleteImage($image->id());
        }

        $this->fileStorage->deleteFoldersByTravelId($travelId);
    }

    public function getTravelLogo(int $travelId): ?TravelImage
    {
        return $this->travelRepository->getTravelLogo($travelId);
    }

    public function getTravelPhotoList(int $travelId): array
    {
        return $this->travelRepository->getTravelPhotoList($travelId);
    }

    public function setAsLogo(int $travelId, int $imageId): void
    {
        $logo = $this->getTravelLogo($travelId);

        if ($logo !== null) {
            $this->travelRepository->saveImage($logo->id(), ['type' => ImageType::PHOTO, 'travel_id' => $travelId]);
        }

        $this->travelRepository->saveImage($imageId, ['type' => ImageType::LOGO, 'travel_id' => $travelId]);
    }
}
