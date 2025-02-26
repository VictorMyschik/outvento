<?php

declare(strict_types=1);

namespace App\Services\Travel;

use App\Models\Travel\Travel;

final readonly class TravelService
{
    public function __construct(
        private TravelRepositoryInterface $travelRepository,
    ) {}

    public function createTravel(array $data): int
    {
        $data['public_id'] = crc32((string)microtime());
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
}
