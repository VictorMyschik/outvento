<?php

namespace App\Services\Albums;


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
}