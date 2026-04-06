<?php

declare(strict_types=1);

namespace App\Repositories\Albums;

use App\Models\Albums\Album;
use App\Models\Albums\AlbumTravel;
use App\Models\Travel\Travel;
use App\Models\Travel\UIT;
use App\Models\User;
use App\Repositories\DatabaseRepository;
use App\Services\Albums\AlbumRepositoryInterface;
use App\Services\Travel\Enum\UserTravelRole;

final readonly class AlbumRepository extends DatabaseRepository implements AlbumRepositoryInterface
{
    public function saveAlbum(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(Album::TABLE)->where('id', $id)->update($data);

            return $id;
        }

        return $this->db->table(Album::TABLE)->insertGetId($data);
    }

    public function deleteAlbum(int $id): void
    {
        $this->db->table(Album::TABLE)->where('id', $id)->delete();
    }

    public function getLinkedTravels(int $albumId): array
    {
        return $this->db->table(Travel::TABLE)
            ->join(UIT::TABLE, function ($join) use ($albumId) {
                $join->on(Travel::TABLE . '.id', '=', UIT::TABLE . '.travel_id')
                    ->where(UIT::TABLE . '.role', UserTravelRole::Owner->value);
            })
            ->join(AlbumTravel::TABLE, AlbumTravel::TABLE . '.travel_id', '=', Travel::TABLE . '.id')
            ->where(AlbumTravel::TABLE . '.album_id', $albumId)
            ->selectRaw(implode(',', [
                AlbumTravel::TABLE . '.id as id',
                Travel::TABLE . '.id as travel_id',
                Travel::TABLE . '.title as travel_title',
                UIT::TABLE . '.user_id as owner_id',
            ]))
            ->get()
            ->toArray();
    }

    public function addAlbumTravel(int $albumId, int $travelId): void
    {
        $this->db->table(AlbumTravel::TABLE)->insertOrIgnore([
            'album_id'  => $albumId,
            'travel_id' => $travelId,
        ]);
    }

    public function getAlbumsForTravel(int $travelId): array
    {
        return $this->db->table(Album::TABLE)
            ->join(AlbumTravel::TABLE, AlbumTravel::TABLE . '.album_id', '=', Album::TABLE . '.id')
            ->join(User::TABLE, User::TABLE . '.id', '=', Album::TABLE . '.user_id')
            ->where(AlbumTravel::TABLE . '.travel_id', $travelId)
            ->selectRaw(implode(',', [
                Album::TABLE . '.id as id',
                Album::TABLE . '.title as title',
                User::TABLE . '.name as user_name',
                User::TABLE . '.id as user_id',
            ]))
            ->get()
            ->toArray();
    }

    public function delinkAlbumTravel(int $travelId, int $albumId): void
    {
        $this->db->table(AlbumTravel::TABLE)
            ->where('travel_id', $travelId)
            ->where('album_id', $albumId)
            ->delete();
    }
}