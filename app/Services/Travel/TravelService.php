<?php

declare(strict_types=1);

namespace App\Services\Travel;

use App\Models\Travel\Travel;
use App\Models\Travel\TravelMedia;
use App\Services\Travel\Enum\MediaType;
use App\Services\Travel\Enum\TravelPointType;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\UserTravelRole;
use Illuminate\Http\UploadedFile;

readonly class TravelService
{
    public function __construct(
        private TravelRepositoryInterface $repository,
        private TravelUploadService       $fileStorage,
    ) {}

    public function createTravel(int $ownerId, array $data): int
    {
        $data['public_id'] = now()->format('dmyHis') . random_int(1, 9);
        $data['status'] = TravelStatus::Draft;
        $data['private_id'] = hash('sha256', $data['public_id'] . config('app.key'));

        $id = $this->repository->saveTravel(0, $data);

        $this->repository->saveTravelUser($ownerId, $id, UserTravelRole::Owner);

        return $id;
    }

    public function updateTravel(int $id, array $data): int
    {
        return $this->repository->saveTravel($id, $data);
    }

    public function updateTravelCountries(int $travelId, array $countryIds): void
    {
        $this->repository->updateTravelCountries($travelId, $countryIds);
    }

    public function updateTravelActivities(int $travelId, array $activityIds): void
    {
        $this->repository->updateTravelActivities($travelId, $activityIds);
    }

    public function updateTravelOwner(int $travelId, int $userId): void
    {
        $this->repository->saveTravelUser($userId, $travelId, UserTravelRole::Owner);
    }

    public function getPublicUrl(Travel $travel): string
    {
        return route('travel.public.link', ['token' => $travel->getPublicId()]);
    }

    public function deleteTravel(int $travelId): void
    {
        $this->deleteTravelMedias($travelId);
        $this->repository->deleteTravel($travelId);
    }

    public function getTravelUsers(Travel $travel): array
    {
        return $this->repository->getTravelUsers($travel);
    }

    public function saveTravelMedia(int $mediaId, Travel $travel, UploadedFile $uploadedFile, MediaType $type): int
    {
        return $this->fileStorage->uploadTravelMedia($mediaId, $uploadedFile, $travel, $type);
    }

    public function updateTravelMedia(int $mediaId, array $data): void
    {
        $this->fileStorage->updateTravelMediaModel($mediaId, $data);
    }

    public function deleteImage(int $mediaId): void
    {
        $this->fileStorage->deleteFile(
            $this->repository->getTravelMedia($mediaId)->path
        );

        $this->repository->deleteTravelMedia($mediaId);
    }

    public function deleteTravelMedias(int $travelId): void
    {
        foreach ($this->getTravelMediaList($travelId) as $image) {
            $this->deleteImage($image->id());
        }

        $this->fileStorage->deleteFoldersByTravelId($travelId);
    }

    public function getTravelLogo(int $travelId): ?TravelMedia
    {
        return $this->repository->getTravelLogo($travelId);
    }

    public function getTravelMediaList(int $travelId): array
    {
        return $this->repository->getTravelMediaList($travelId);
    }

    public function setAsLogo(int $imageId): void
    {
        $this->repository->setAsLogo($imageId);
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

    public function getFullTravelMediaSize(int $travelId): int
    {
        return $this->repository->getFullTravelMediaSize($travelId);
    }

    public function getFullTravelMediaSizeInMb(int $travelId): string
    {
        return number_format(round($this->getFullTravelMediaSize($travelId) / 1024 / 1024, 2), 2, '.', ' ') . ' MB';
    }

    public function getTravelPoints(int $travelId): array
    {
        return $this->repository->getTravelPoints($travelId);
    }

    public function savePoint(int $pointId, int $travelId, TravelPointType $type, array $data): int
    {
        return $this->repository->savePoint(
            pointId: $pointId,
            travelId: $travelId,
            type: $type,
            data: $data
        );
    }

    public function deletePoint(int $pointId): void
    {
        $this->repository->deletePoint($pointId);
    }

    public function deleteTravelPoints(int $travelId): void
    {
        $this->repository->deleteTravelPoints($travelId);
    }
}
