<?php

namespace App\Services\Albums;

interface AlbumRepositoryInterface
{
    public function saveAlbum(int $id, array $data): int;

    public function deleteAlbum(int $id): void;

    public function getLinkedTravels(int $albumId): array;

    public function addAlbumTravel(int $albumId, int $travelId): void;

    public function getAlbumsForTravel(int $travelId): array;

    public function delinkAlbumTravel(int $travelId, int $albumId): void;
}