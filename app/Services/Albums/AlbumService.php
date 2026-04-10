<?php

declare(strict_types=1);

namespace App\Services\Albums;

use App\Jobs\Images\AlbumAvatarResizeJob;
use App\Jobs\Images\AlbumImageResizeJob;
use App\Models\Albums\Album;
use App\Models\Albums\AlbumMedia;
use App\Models\Travel\Travel;
use App\Models\User;
use App\Services\Albums\Enum\Icon;
use App\Services\Albums\Enum\Visibility;
use App\Services\Image\Enum\Size;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Psr\SimpleCache\CacheInterface;
use stdClass;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final readonly class AlbumService
{
    public function __construct(
        private AlbumRepositoryInterface $repository,
        private AlbumUploadService       $uploadService,
        private CacheInterface           $cache,
    ) {}

    public function getAlbumById(int $id): stdClass
    {
        return $this->repository->getAlbumById($id);
    }

    public function getMediaById(int $id): stdClass
    {
        return $this->repository->getAlbumMediaById($id);
    }

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

    public function addAvatar(Album $album, UploadedFile $file): void
    {
        $path = $this->uploadService->addAvatar($album->id, $file);

        if ($path) {
            $this->removeAvatar($album);

            $this->repository->saveAlbum($album->id, ['avatar' => $path]);

            AlbumAvatarResizeJob::dispatch($album->id);
        }
    }

    public function removeAvatar(Album $album): void
    {
        if (!$album->avatar) {
            return;
        }

        if ($this->uploadService->smartDeleteAvatar($album->id, $album->avatar)) {
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
        $id = $this->uploadService->uploadAlbumFile($albumId, $file);

        AlbumImageResizeJob::dispatch($id);
    }

    public function getAlbumFileSize(int $albumId): int
    {
        return $this->repository->getAlbumFileSize($albumId);
    }

    public function getAlbumMedia(int $albumId): array
    {
        return $this->repository->getAlbumMedia($albumId);
    }

    public function getAlbumMediaById(int $mediaId): ?stdClass
    {
        return $this->repository->getAlbumMediaById($mediaId);
    }

    public function canViewAlbumMedia(int $mediaId, ?User $user): bool
    {
        $media = $this->repository->getAlbumMediaById($mediaId);

        if (!$media) {
            return false;
        }

        $album = $this->repository->getAlbumById((int)$media->album_id);

        if (!$album) {
            return false;
        }

        return match (Visibility::from($album->visibility)) {
            Visibility::Public => true,
            Visibility::RegisteredUsers => $user !== null,
            Visibility::Private => (int)$album->user_id === (int)$user?->id,
        };
    }

    public function canCommentOnAlbumMedia(int $mediaId, ?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->canViewAlbumMedia($mediaId, $user);
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

    public function generateMediaUrl(AlbumMedia $media, Size $size): string
    {
        $expires = now()->addMinutes(1)->timestamp;

        return route('api.v1.album.media', [
            'm'   => $media->id,
            'p'   => $media->path,
            's'   => $size->value,
            'e'   => $expires,
            'sig' => $this->getSignature(mediaId: $media->id, path: $media->path, size: $size, expires: $expires),
        ]);
    }

    public function getSignature(int $mediaId, string $path, Size $size, int $expires): string
    {
        $data = implode('|', [$mediaId, $path, $size->value, $expires]);

        return hash_hmac('sha256', $data, config('app.key'));
    }

    public function checkMediaSignature(string $signature, int $mediaId, string $path, Size $size, int $expires): void
    {
        if (!hash_equals($this->getSignature($mediaId, $path, $size, $expires), $signature)) {
            abort(403);
        }
    }

    public function getMediaUrl(string $basePath, Size $size): BinaryFileResponse
    {
        if ($size === Size::Original) {
            return $this->uploadService->getUrl($basePath);
        }

        $info = pathinfo($basePath);

        $resizedRelativePath = sprintf('%s/%s_%s.webp', $info['dirname'], $info['filename'], $size->value);

        return $this->uploadService->getUrl($resizedRelativePath) ?: $this->uploadService->getUrl($basePath);
    }

    public function deleteMedia(stdClass $album, stdClass $media): void
    {
        if ($this->uploadService->smartDeleteFile($album, $media)) {
            $this->repository->deleteAlbumAttachment($media->id);
        }
    }

    public function purgeAlbumMedia(int $albumId): void
    {
        $album = $this->repository->getAlbumById($albumId);

        foreach ($this->repository->getAlbumMedia($albumId) as $media) {
            $this->deleteMedia($album, $media);
        }
    }

    public function updateMediaInfo(int $mediaId, array $data): void
    {
        $this->repository->updateMediaInfo($mediaId, $data);
    }

    public function deleteTempFile(string $relativePath): void
    {
        $this->uploadService->deleteFile($relativePath);
    }

    public function getUserLike(int $mediaId, int $userId): ?Icon
    {
        return $this->repository->getUserLike($mediaId, $userId);
    }

    public function doMediaLike(int $mediaId, int $userId, Icon $icon): void
    {
        $this->repository->toggleMediaLike($mediaId, $userId, $icon);
        $this->clearLikeCache($mediaId);
    }

    public function deleteLike(int $mediaId, int $userId): void
    {
        $this->repository->deleteLike($mediaId, $userId);
        $this->clearLikeCache($mediaId);
    }

    public function getAggregatedLikesInfo(int $mediaId): array
    {
        return $this->cache->remember('media_likes_aggregation_' . $mediaId, 60 * 60 * 24, function () use ($mediaId) {
            return $this->repository->getAggregatedLikes($mediaId);
        });
    }

    private function clearLikeCache(int $mediaId): void
    {
        $this->cache->forget('media_likes_aggregation_' . $mediaId);
    }

    public function getLikeUserList(int $mediaId): array
    {
        return $this->repository->getLikesList($mediaId);
    }

    public function setMediaAsAvatar(int $albumId, int $mediaId): void
    {
        $this->repository->setMediaAsAvatar($albumId, $mediaId);
    }
}