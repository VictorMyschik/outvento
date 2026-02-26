<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\Reference\UserLocation;
use App\Repositories\DatabaseRepository;

final readonly class UserLocationRepository extends DatabaseRepository
{
    public function setLocation(int $userId, int $cityId, float $lat, float $lng): void
    {
        $location = UserLocation::updateOrCreate([
            'user_id' => $userId
        ], [
            'city_id' => $cityId,
            'lat'     => $lat,
            'lng'     => $lng,
        ]);

        $this->db->statement(
            '
                UPDATE user_locations
                SET geom = ST_SetSRID(
                    ST_MakePoint(?, ?),
                    4326
                )::geography
                WHERE id = ?
                ',
            [$lng, $lat, $location->id]
        );
    }

    public function deleteUserLocation(int $userId): void
    {
        $this->db->table(UserLocation::getTableName())->where('user_id', $userId)->delete();
    }
}