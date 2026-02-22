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
    private const string TRAVEL_PATH = 'travels';

    public function __construct(
        private Filesystem                $filesystem,
        private TravelRepositoryInterface $repository,
        private array                     $basePaths,
    ) {}

    private function uploadFiles(UploadedFile $file, string $path): void
    {
        $resulFileName = $file->getClientOriginalName();
        $filePathWithName = $path . '/' . $resulFileName;

        $this->filesystem->put($filePathWithName, $file->getContent());
    }

    public function uploadTravelMedia(int $mediaId, UploadedFile $file, Travel $travel, MediaType $type): int
    {
        $path = $this->getPath($travel->id(), $file->getClientOriginalName());
        $this->uploadFiles($file, $path);

        if ($mediaId){
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

    private function getPath(int $travelId, string $fileName): string
    {
        $basePath = $this->basePaths['media'] . '/' . $travelId;

        $list = array_reverse(Storage::directories($basePath));

        foreach ($list as $dir) {
            if (Storage::exists($dir . '/' . $fileName) || count(Storage::files($dir)) >= 3) {
                continue;
            }

            return $dir;
        }

        $currentDir = count($list) + 1;

        return $basePath . '/' . $currentDir;
    }

    public function deleteFile(string $filePathWithName): void
    {
        $this->filesystem->delete($filePathWithName);
    }

    public function deleteFoldersByTravelId(int $travelId): bool
    {
        $basePath = self::TRAVEL_PATH . '/' . $travelId;

        return $this->filesystem->deleteDirectory($basePath);
    }
}
