<?php

declare(strict_types=1);

namespace App\Services\Travel;

use App\Models\Travel\Travel;
use App\Models\Travel\TravelMedia;
use App\Models\Travel\TravelResource;
use App\Services\Location\LocationService;
use App\Services\Travel\DTO\TravelPointDto;
use App\Services\Travel\Enum\MediaType;
use App\Services\Travel\Enum\TravelPointType;
use App\Services\Travel\Enum\TravelResourceType;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\UserTravelRole;
use App\Services\User\Google\DTO\CityLocationDto;
use Illuminate\Http\UploadedFile;

readonly class TravelService
{
    public function __construct(
        private TravelRepositoryInterface $repository,
        private TravelUploadService       $fileStorage,
        private LocationService           $location,
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
        $this->repository->deleteTravelMedias($travelId);

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
        return $this->repository->getTravelMediaSize($travelId);
    }

    public function getFullUserMediaSize(int $userId): int
    {
        return $this->repository->getFullUserMediaSize($userId);
    }

    public function getFullTravelMediaSizeInMb(int $travelId): string
    {
        return number_format(round($this->getFullTravelMediaSize($travelId) / 1024 / 1024, 2), 2, '.', ' ') . ' MB';
    }

    public function getTravelPoints(int $travelId): array
    {
        return $this->repository->getTravelPoints($travelId);
    }

    public function savePoint(int $pointId, TravelPointType $type, CityLocationDto $dto, TravelPointDto $data): int
    {
        $city = $this->location->getCityExt($dto);

        $data->setCityId($city->id);

        return $this->repository->savePoint($pointId, $type, $data);
    }

    public function deleteTravelPoint(int $pointId): void
    {
        $this->repository->deletePoint($pointId);
    }

    public function deleteTravelPoints(int $travelId): void
    {
        $this->repository->deleteTravelPoints($travelId);
    }

    public function saveTravelResource(int $resourceId, int $travelId, TravelResourceType $type, array $data): void
    {
        $insert = [
            'travel_id' => $travelId,
            'type'      => $type->value,
            'title'     => (string)$data['title'],
            'path'      => (string)($data['path'] ?? null),
            'sort'      => (int)$data['sort'],
            'user_id'   => (int)$data['user_id'],
        ];

        if ($type === TravelResourceType::File) {
            $insert['path'] = $this->fileStorage->uploadTravelResourceFile($travelId, $data['file']);
            $insert['size'] = $data['file']->getSize();
        }

        $this->repository->saveTravelResource($resourceId, $insert);
    }

    public function getTravelLinks(int $travelId): array
    {
        return $this->repository->getTravelLinks($travelId);
    }

    public function getResources(int $travelId): array
    {
        return $this->repository->getResources($travelId);
    }

    public function deleteTravelResource(int $resourceId): void
    {
        $service = TravelResource::loadByOrDie($resourceId);

        if ($service->getType() === TravelResourceType::File) {
            $this->fileStorage->deleteFile($service->path);
        }

        $this->repository->deleteTravelResource($resourceId);
    }

    public function deleteTravelResources(int $travelId): void
    {
        $this->fileStorage->deleteTravelResourcesByTravelId($travelId);
        $this->repository->deleteTravelResources($travelId);
    }

    public function getTravelResourcesSize(int $travelId): int
    {
        return $this->repository->getTravelResourcesSize($travelId);
    }

    public function getTravelResourcesSizeDisplay(int $travelId): string
    {
        return number_format(round($this->getTravelResourcesSize($travelId) / 1024 / 1024, 2), 2, '.', ' ') . ' MB';
    }
}
