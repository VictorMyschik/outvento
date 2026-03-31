<?php

declare(strict_types=1);

namespace App\Services\Travel;

use App\Models\Travel\Travel;
use App\Models\Travel\TravelMedia;
use App\Services\Travel\Enum\MediaType;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final readonly class TravelUploadService
{
    public function __construct(
        private Filesystem                $filesystem,
        private TravelRepositoryInterface $repository,
        private array                     $basePaths,
    ) {}

    private function uploadFile(UploadedFile $file, string $path): void
    {
        $resulFileName = $file->getClientOriginalName();
        $filePathWithName = $path . '/' . $resulFileName;

        $this->filesystem->put($filePathWithName, $file->getContent());
    }

    public function uploadTravelResourceFile(int $travelId, UploadedFile $file): string
    {
        $path = $this->getPath($travelId, $file->getClientOriginalName(), 'resources');
        $this->uploadFile($file, $path);

        return $path . '/' . $file->getClientOriginalName();
    }

    public function uploadTravelMedia(int $mediaId, UploadedFile $file, Travel $travel, MediaType $type): int
    {
        $path = $this->getPath($travel->id(), $file->getClientOriginalName(), 'media');
        $this->uploadFile($file, $path);

        if ($mediaId) {
            $this->deleteFile($this->repository->getTravelMedia($mediaId)->path);
        }

        return $this->updateTravelMediaModel($mediaId, [
            'travel_id'  => $travel->id(),
            'path'       => $path . '/' . $file->getClientOriginalName(),
            'size'       => $file->getSize(),
            'media_type' => $type->value,
        ]);
    }

    public function updateTravelMediaModel(int $mediaId, array $data): int
    {
        return $this->repository->saveImage($mediaId, $data);
    }

    private function getPath(int $travelId, string $fileName, string $type): string
    {
        $basePath = $travelId . '/' . $this->basePaths[$type];

        $directories = $this->filesystem->directories($basePath);
        sort($directories);

        foreach ($directories as $dir) {
            if ($this->filesystem->exists($dir . '/' . $fileName) || count($this->filesystem->files($dir)) >= 100) {
                continue;
            }

            return $dir;
        }

        $currentDir = count($directories) + 1;

        return $basePath . '/' . $currentDir;
    }

    public function deleteFile(string $filePathWithName): void
    {
        $this->filesystem->delete($filePathWithName);
    }

    public function deleteFoldersByTravelId(int $travelId): bool
    {
        return $this->filesystem->deleteDirectory($travelId);
    }

    public function deleteTravelResourcesByTravelId(int $travelId): void
    {
        $basePath = $travelId . '/' . $this->basePaths['resources'];

        $this->filesystem->deleteDirectory($basePath);
    }
}
