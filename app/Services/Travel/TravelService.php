<?php

declare(strict_types=1);

namespace App\Services\Travel;

use App\Models\Travel\Travel;
use App\Models\Travel\TravelImage;
use App\Services\Travel\Enum\ImageType;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\UserTravelRole;
use Illuminate\Http\UploadedFile;

readonly class TravelService
{
    public function __construct(
        private TravelRepositoryInterface $travelRepository,
        private TravelUploadService       $fileStorage,
    ) {}

    public function createTravel(int $ownerId, array $data): int
    {
        $data['public_id'] = now()->format('dmyHis') . random_int(1, 9);
        $data['status'] = TravelStatus::Draft;

        $id = $this->travelRepository->saveTravel(0, $data);

        $this->travelRepository->saveTravelUser($ownerId, $id, UserTravelRole::Owner);

        return $id;
    }

    public function updateTravel(int $id, array $data): int
    {
        return $this->travelRepository->saveTravel($id, $data);
    }

    public function updateTravelCountries(int $travelId, array $countryIds): void
    {
        $this->travelRepository->updateTravelCountries($travelId, $countryIds);
    }

    public function updateTravelActivities(int $travelId, array $activityIds): void
    {
        $this->travelRepository->updateTravelActivities($travelId, $activityIds);
    }

    public function updateTravelOwner(int $travelId, int $userId): void
    {
        $this->travelRepository->saveTravelUser($userId, $travelId, UserTravelRole::Owner);
    }

    public function getPublicUrl(Travel $travel): string
    {
        return route('travel.public.link', ['token' => $travel->getPublicId()]);
    }

    public function deleteTravel(int $travelId): void
    {
        $this->deleteTravelImages($travelId);
        $this->travelRepository->deleteTravel($travelId);
    }

    public function getTravelUsers(Travel $travel): array
    {
        return $this->travelRepository->getTravelUsers($travel);
    }

    public function saveTravelImage(Travel $travel, UploadedFile $uploadedFile, ImageType $type): int
    {
        return $this->fileStorage->uploadTravelImage($uploadedFile, $travel, $type);
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

    public function cloneTravel(Travel $travel): int
    {
        $data = $travel->only([
            'date_from',
            'date_to',
            'status',
            'visible',
            'title',
            'preview',
            'description',
        ]);

        $newTravelId = $this->createTravel($travel->getOwnerId(), $data);

        $this->updateTravelCountries($newTravelId, $travel->getCountries()->pluck('id')->toArray());
        $this->updateTravelActivities($newTravelId, $travel->getActivities()->pluck('id')->toArray());

        return $newTravelId;
    }
}
