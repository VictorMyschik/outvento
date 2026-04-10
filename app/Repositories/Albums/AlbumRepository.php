<?php

declare(strict_types=1);

namespace App\Repositories\Albums;

use App\Models\Albums\Album;
use App\Models\Albums\AlbumMedia;
use App\Models\Albums\AlbumMediaLike;
use App\Models\Albums\AlbumTravel;
use App\Models\Travel\Travel;
use App\Models\Travel\UIT;
use App\Models\User;
use App\Repositories\DatabaseRepository;
use App\Services\Albums\AlbumRepositoryInterface;
use App\Services\Albums\Enum\Icon;
use App\Services\Travel\Enum\UserTravelRole;
use stdClass;

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

    public function addAlbumAttachment(array $data): int
    {
        return $this->db->table(AlbumMedia::TABLE)->insertGetId($data);
    }

    public function findExistsAttachment(int $albumId, string $hash, ?int $selfId = null): ?stdClass
    {
        return $this->db->table(AlbumMedia::TABLE)
            ->when($selfId !== null, function ($q) use ($selfId) {
                $q->whereNot('id', $selfId);
            })
            ->where('album_id', $albumId)
            ->where('hash', $hash)
            ->first();
    }

    public function getAlbumFileSize(int $albumId): int
    {
        return (int)$this->db->table(AlbumMedia::TABLE)
            ->where('album_id', $albumId)
            ->sum('size');
    }

    public function getAlbumMedia(int $albumId): array
    {
        return $this->db->table(AlbumMedia::TABLE)
            ->where('album_id', $albumId)
            ->selectRaw(implode(',', [
                'id',
                'file_type',
                'path',
                'size',
                'hash',
                'mime',
                'description',
                'created_at',
                'updated_at',
                'point',
            ]))
            ->orderBy('sort')
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }

    public function getAlbumById(int $albumId): ?stdClass
    {
        return $this->db->table(Album::TABLE)->where('id', $albumId)->first();
    }

    public function getAlbumMediaById(int $mediaId): ?stdClass
    {
        return $this->db->table(AlbumMedia::TABLE)
            ->where('id', $mediaId)
            ->first(['path', 'album_id', 'hash', 'id']);
    }

    public function deleteAlbumAttachment(int $mediaId): void
    {
        $this->db->table(AlbumMedia::TABLE)->where('id', $mediaId)->delete();
    }

    public function updateMediaInfo(int $mediaId, array $data): void
    {
        $data['point'] = null;

        if (!empty($data['address'])) {
            $data['point'] = $this->db->raw(
                sprintf(
                    'ST_SetSRID(ST_MakePoint(%F, %F), 4326)::geography',
                    $data['lng'],
                    $data['lat'],
                )
            );
        }

        unset($data['lat'], $data['lng']);

        $this->db->table(AlbumMedia::TABLE)->where('id', $mediaId)->update($data);
    }

    public function getLikesList(int $mediaId): array
    {
        return [];
    }

    public function toggleMediaLike(int $mediaId, int $userId, Icon $icon): void
    {
        $sql = <<<SQL
        WITH existing AS (
            SELECT icon
            FROM album_media_likes
            WHERE media_id = :media_id
              AND user_id = :user_id
        ),
        deleted AS (
            DELETE FROM album_media_likes
            WHERE media_id = :media_id
              AND user_id = :user_id
              AND icon = :icon
            RETURNING 1
        ),
        upsert AS (
            INSERT INTO album_media_likes (media_id, user_id, icon)
            SELECT :media_id, :user_id, :icon
            WHERE NOT EXISTS (SELECT 1 FROM existing WHERE icon = :icon)
            ON CONFLICT (media_id, user_id)
            DO UPDATE SET icon = EXCLUDED.icon
            WHERE album_media_likes.icon <> EXCLUDED.icon
            RETURNING 1
        )
        SELECT
            (SELECT COUNT(*) FROM deleted) AS deleted,
            (SELECT COUNT(*) FROM upsert) AS upserted;
        SQL;

        $this->db->insert($sql, [
            'media_id' => $mediaId,
            'user_id'  => $userId,
            'icon'     => $icon->value,
        ]);
    }

    public function getUserLike(int $mediaId, int $userId): ?Icon
    {
        $result = $this->db->table(AlbumMediaLike::TABLE)
            ->where('media_id', $mediaId)
            ->where('user_id', $userId)
            ->value('icon');

        return $result ? Icon::from($result) : null;
    }

    public function getAggregatedLikes(int $mediaId): array
    {
        return $this->db->table(AlbumMediaLike::TABLE)
            ->where('media_id', $mediaId)
            ->selectRaw('COUNT(*) as likes_count, icon')
            ->groupBy('icon')
            ->get()
            ->toArray();
    }

    public function deleteLike(int $mediaId, int $userId): void
    {
        $this->db->table(AlbumMediaLike::TABLE)->where('media_id', $mediaId)->where('user_id', $userId)->delete();
    }

    public function setMediaAsAvatar(int $albumId, int $mediaId): void
    {
        $sql = <<<SQL
            UPDATE albums
            SET avatar = (select path from album_media where id = :media_id)
            WHERE id = :album_id;
        SQL;

        $this->db->statement($sql, ['media_id' => $mediaId, 'album_id' => $albumId]);
    }

    public function hasMediaByPath(int $albumId, string $path): bool
    {
        return $this->db->table(AlbumMedia::TABLE)
            ->where('album_id', $albumId)
            ->where('path', $path)
            ->exists();
    }
}