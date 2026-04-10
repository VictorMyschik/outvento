<?php

namespace App\Services\Albums;


use App\Services\Albums\Enum\Icon;
use stdClass;

interface AlbumRepositoryInterface
{
    public function getAlbumById(int $albumId): ?stdClass;

    public function getAlbumMediaById(int $mediaId): ?stdClass;

    public function saveAlbum(int $id, array $data): int;

    public function deleteAlbum(int $id): void;

    public function getLinkedTravels(int $albumId): array;

    public function addAlbumTravel(int $albumId, int $travelId): void;

    public function getAlbumsForTravel(int $travelId): array;

    public function delinkAlbumTravel(int $travelId, int $albumId): void;

    public function addAlbumAttachment(array $data): int;

    public function deleteAlbumAttachment(int $mediaId): void;

    public function findExistsAttachment(int $albumId, string $hash, ?int $selfId = null): ?stdClass;

    public function getAlbumFileSize(int $albumId): int;

    public function getAlbumMedia(int $albumId): array;

    public function getUserLike(int $mediaId, int $userId): ?Icon;

    public function updateMediaInfo(int $mediaId, array $data): void;

    public function getLikesList(int $mediaId): array;

    public function getAggregatedLikes(int $mediaId): array;

    public function toggleMediaLike(int $mediaId, int $userId, Icon $icon): void;

    public function deleteLike(int $mediaId, int $userId): void;

    public function setMediaAsAvatar(int $albumId, int $mediaId): void;

    public function hasMediaByPath(int $albumId, string $path): bool;
}