<?php

declare(strict_types=1);

namespace App\Services\Albums;

use App\Models\Albums\Album;
use App\Models\Travel\Travel;
use App\Models\User;
use App\Services\Albums\Enum\Visibility;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final readonly class AlbumService
{
    public function __construct(
        private AlbumRepositoryInterface $repository,
        private AlbumUploadService       $uploadService,
    ) {}

    public function saveAlbum(int $id, string $title, Visibility $visibility, User $user, ?string $description = null): int
    {
        return $this->repository->saveAlbum($id, [
            'title'       => $title,
            'visibility'  => $visibility->value,
            'user_id'     => $user->id,
            'description' => $description,
        ]);
    }

    public function deleteAlbum(Album $album): void
    {
        if ($album->avatar) {
            $this->removeAvatar($album);
        }

        $this->repository->deleteAlbum($album->id);
    }

    public function showAvatar(Album $album, ?User $user): ?BinaryFileResponse
    {
        switch ($album->getVisibility()) {
            case Visibility::Public:
            case Visibility::RegisteredUsers:
                return $this->getAvatarExt($album);
            case Visibility::Private:
                return $user?->id === $album->user_id ? $this->getAvatarExt($album) : null;
        }

        return null;
    }

    private function getAvatarExt(Album $album): BinaryFileResponse
    {
        if ($album->avatar) {
            return Response::file(Storage::disk('albums')->path($album->avatar), [
                'Content-Type'        => mime_content_type(Storage::disk('albums')->path($album->avatar)),
                'Content-Disposition' => 'inline; filename="' . basename($album->avatar) . '"',
            ]);
        }

        return $this->getDefaultAvatar();
    }

    public function getDefaultAvatar(): BinaryFileResponse
    {
        return Response::file(Storage::disk('public')->path('/images/album/album_blank.webp'), [
            'Content-Type'        => 'image/webp',
            'Content-Disposition' => 'inline; filename="album_blank.webp"',
        ]);
    }

    public function addAvatar(int $albumId, UploadedFile $file): void
    {
        $path = $this->uploadService->saveAvatar($albumId, $file);
        $this->repository->saveAlbum($albumId, ['avatar' => $path]);
    }

    public function removeAvatar(Album $album): void
    {
        $result = $this->uploadService->deleteFile($album->avatar);

        if ($result) {
            $this->repository->saveAlbum($album->id, ['avatar' => null]);
        }
    }

    public function getLinkedTravels(int $albumId): array
    {
        return $this->repository->getLinkedTravels($albumId);
    }

    public function addAlbumTravel(int $albumId, int $travelId): void
    {
        $this->repository->addAlbumTravel($albumId, $travelId);
    }

    public function getAlbumsForTravel(Travel $travel): array
    {
        return $this->repository->getAlbumsForTravel($travel->id);
    }

    public function delinkAlbumTravel(int $travelId, int $albumId): void
    {
        $this->repository->delinkAlbumTravel($travelId, $albumId);
    }

    public function uploadMedia(int $albumId, UploadedFile $file): void
    {
        $this->uploadService->uploadAlbumFile($albumId, $file);
    }

    public function getAlbumFileSize(int $albumId): int
    {
        return $this->repository->getAlbumFileSize($albumId);
    }

    public function getAlbumMedia(int $albumId): array
    {
        return $this->repository->getAlbumMedia($albumId);
    }

    public function showMedia(int $albumId, int $mediaId, ?User $user): ?BinaryFileResponse
    {
        $album = $this->repository->getAlbumById($albumId);

        if ($album->user_id !== $user?->id) {
            return null;
        }

        $media = $this->repository->getAlbumMediaById($mediaId);

        if (!$media) {
            return null;
        }

        return Response::file(Storage::disk('albums')->path($media->path), [
            'Content-Type'        => mime_content_type(Storage::disk('albums')->path($media->path)),
            'Content-Disposition' => 'inline; filename="' . basename($media->path) . '"',
        ]);
    }

    public function getSignature(int $mediaId, int $exp): string
    {
        return hash_hmac(
            'sha256',
            $mediaId . '|' . $exp,
            config('app.key')
        );
    }

    public function deleteMedia(int $mediaId): void
    {
        if ($this->uploadService->smartDeleteFile($this->repository->getAlbumMediaById($mediaId))) {
            $this->repository->deleteAlbumAttachment($mediaId);
        }
    }


    public function purgeAlbumMedia(int $albumId): void
    {
        foreach ($this->repository->getAlbumMedia($albumId) as $media) {
            $this->deleteMedia($media->id);
        }
    }

    public function updateMediaInfo(int $mediaId, array $data): void
    {
        $this->repository->updateMediaInfo($mediaId, $data);
    }
}