<?php

declare(strict_types=1);

namespace App\Services\User;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;

final readonly class UserUploadService
{
    public function __construct(
        private Filesystem $filesystem,
        private array      $basePaths,
    ) {}

    public function saveAvatar(int $userId, UploadedFile $file): string
    {
        $path = $this->getPath($userId, 'avatar');
        $filename = 'avatar' . '.' . $file->getClientOriginalExtension();
        $this->filesystem->putFileAs($path, $file, $filename);

        return $path . '/' . $filename;
    }

    public function deleteFile(string $path): void
    {
        $this->filesystem->delete($path);
    }

    public function getPath(int $userId, string $key): string
    {
        $path = $userId;

        if ($this->basePaths[$key]) {
            $path .= '/' . $key;
        }

        $path .= $this->basePaths[$key];

        return $path;
    }
}