<?php

declare(strict_types=1);

namespace App\Repositories\Constructor;

use App\Models\Constructor\ConstructorFile;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final readonly class ConstructorFileStorage
{
    public function __construct(
        private Filesystem $filesystem,
        private string     $basePath,
    ) {}

    public function deleteFile(string $filePathWithName): void
    {
        $this->filesystem->delete($filePathWithName);
    }

    public function getFilePathToSave(string $name, string $baseDir): string
    {
        $list = array_reverse(Storage::directories($baseDir));

        foreach ($list as $dir) {
            if (Storage::exists($dir . '/' . $name) || count(Storage::files($dir)) >= 50) {
                continue;
            }

            return $dir;
        }

        $currentDir = count($list) + 1;

        return $baseDir . '/' . $currentDir;
    }

    public function saveFile(int $constructorId, int $itemId, UploadedFile $file): string
    {
        $baseDirFromConfig = $this->getBasepath($constructorId, $itemId);

        $path = $this->getFilePathToSave($file->getClientOriginalName(), $baseDirFromConfig);
        $pathWithName = $path . '/' . $file->getClientOriginalName();
        $this->filesystem->put($pathWithName, $file->getContent());

        return $pathWithName;
    }

    public function deleteFileById(ConstructorFile $file): void
    {
        $this->deleteFile($file->path);
    }

    public function clearDirectory(string $path): void
    {
        $this->filesystem->deleteDirectory($path);
    }

    private function getBasepath(int $constructorId, int $itemId): string
    {
        return $this->basePath . '/constructor_' . $constructorId . '/item_' . $itemId;
    }
}
