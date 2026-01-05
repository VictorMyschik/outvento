<?php

declare(strict_types=1);

namespace App\Repositories\Constructor\Storage;

use App\Models\Constructor\ConstructorItemSlide;
use App\Models\Constructor\Video;
use App\Repositories\Constructor\Storage\Enum\StorageFileTypeEnum;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final readonly class FileStorage
{
    public function __construct(
        private Filesystem $filesystem
    ) {}

    public function uploadFiles(UploadedFile $file, string $path): string
    {
        $resulFileName = $file->getClientOriginalName();
        $filePathWithName = $path . '/' . $resulFileName;
        $this->filesystem->put($filePathWithName, $file->getContent());

        return $resulFileName;
    }

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

    public function saveFile(UploadedFile $file, StorageFileTypeEnum $type, string $baseDir, array $data = []): int
    {
        $baseDirFromConfig = match ($type) {
            StorageFileTypeEnum::Video => config('storage.videos'),
            StorageFileTypeEnum::SlideImage => config('storage.images'),
        };

        $path = $this->getFilePathToSave($file->getClientOriginalName(), $baseDirFromConfig);
        $this->uploadFiles($file, $path);

        return match ($type) {
            StorageFileTypeEnum::Video => $this->createFileModelVideo($file, $path),
            StorageFileTypeEnum::SlideImage => $this->createFileModelSlideImage($file, $path, $data),
        };
    }

    private function createFileModelVideo(UploadedFile $file, string $path): int
    {
        $model = new Video();
        $model->setFileName($file->getClientOriginalName());
        $model->setPath($path);
        $model->setSize($file->getSize());
        $model->setExtension($file->extension());

        return $model->saveMr();
    }

    public function createFileModelSlideImage(UploadedFile $file, string $path, array $data): int
    {
        $model = new ConstructorItemSlide();
        $model->setSliderID($data['slider_id']);
        $model->setFileName($file->getClientOriginalName());
        $model->setDisplayName($data['display_name'] ?: null);
        $model->setPath($path);
        $model->setSort($data['sort'] ?? 0);
        $model->setAlt($data['alt'] ?? null);

        return $model->saveMr();
    }

    public function deleteFileById(int $id, StorageFileTypeEnum $type): void
    {
        $file = $this->getFileByID($id, $type);
        $this->deleteFile($file->getFilePathWithName());
        $file->delete();
    }

    public function getFileByID(int $id, StorageFileTypeEnum $type): ?object
    {
        return match ($type) {
            StorageFileTypeEnum::Video => Video::loadBy($id),
            StorageFileTypeEnum::SlideImage => ConstructorItemSlide::loadBy($id),
            default => throw new \Exception('Invalid type'),
        };
    }

    public function clearDirectory(string $path): void
    {
        $this->filesystem->deleteDirectory($path);
    }
}
