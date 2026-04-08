<?php

declare(strict_types=1);

namespace App\Services\Albums;

use App\Services\Albums\Enum\FileType;
use App\Services\Upload\UploadBaseService;
use finfo;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use stdClass;

final readonly class AlbumUploadService extends UploadBaseService
{
    public function __construct(
        protected Filesystem             $filesystem,
        private AlbumRepositoryInterface $repository,
        protected array                  $basePaths,
    ) {}

    public function validateFile(UploadedFile $file): void
    {
        if ($file->getSize() > 100 * 1024 * 1024) {
            throw new InvalidArgumentException('File size exceeds the maximum allowed size of 100 MB.');
        }
    }

    public function saveAvatar(int $albumId, UploadedFile $file): string
    {
        $path = $this->getPath($albumId, 'avatar');
        $filename = 'avatar' . '.' . $file->getClientOriginalExtension();
        $this->filesystem->putFileAs($path, $file, $filename);

        return $path . '/' . $filename;
    }

    private function uploadFile(UploadedFile $file, string $filePathWithName): void
    {
        $this->filesystem->put($filePathWithName, $file->getContent());
    }

    public function uploadAlbumFile(int $albumId, UploadedFile $file): int
    {
        $hash = md5_file($file->getRealPath());

        $existsFile = $this->findExistsAttachment($albumId, $hash);
        $filePathWithName = $existsFile?->path;
        $fileName = $file->getClientOriginalName();

        if (!$existsFile) {
            $path = $this->getPath($albumId, $fileName);
            $filePathWithName = $path . '/' . $fileName;

            $this->uploadFile($file, $filePathWithName);
        }

        return $this->repository->addAlbumAttachment([
            'album_id'  => $albumId,
            'file_type' => $this->getFileType($file)->value,
            'mime'      => $file->getMimeType(),
            'size'      => $file->getSize(),
            'path'      => $filePathWithName,
            'hash'      => $hash,
        ]);
    }

    public function getFileType(UploadedFile $file): FileType
    {
        if (str_starts_with($file->getMimeType(), 'image/')) {
            return FileType::Image;
        }

        if (str_starts_with($file->getMimeType(), 'video/')) {
            return FileType::Video;
        }

        throw new \LogicException('Unsupported file type: ' . $file->getMimeType());
    }

    public function deleteFile(string $filePathWithName): bool
    {
        return $this->filesystem->delete($filePathWithName);
    }


    private function getPath(int $objectId, string $fileName): string
    {
        $directories = $this->filesystem->directories((string)$objectId);
        sort($directories);

        foreach ($directories as $dir) {
            if ($this->filesystem->exists($dir . '/' . $fileName) || count($this->filesystem->files($dir)) >= 100) {
                continue;
            }

            return $dir;
        }

        $currentDir = count($directories) + 1;

        return $objectId . '/' . $currentDir;
    }

    public function smartDeleteFile(stdClass $file): bool
    {
        $existsFile = $this->repository->findExistsAttachment((int)$file->album_id, $file->hash, $file->id);

        if ($existsFile) {
            return true;
        }

        return $this->deleteFile($file->path);
    }

    public function findExistsAttachment(int $albumId, string $hash): ?stdClass
    {
        return $this->repository->findExistsAttachment($albumId, $hash);
    }
}
