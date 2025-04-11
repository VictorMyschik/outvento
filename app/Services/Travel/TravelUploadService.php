<?php

declare(strict_types=1);

namespace App\Services\Travel;

use App\Models\Travel\Travel;
use App\Services\Travel\Enum\ImageType;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final readonly class TravelUploadService
{
    private const string TRAVEL_PATH = 'travels';

    public function __construct(
        private Filesystem                $filesystem,
        private TravelRepositoryInterface $repository,
    ) {}

    private function uploadFiles(UploadedFile $file, string $path): void
    {
        $resulFileName = $file->getClientOriginalName();
        $filePathWithName = $path . '/' . $resulFileName;

        $this->filesystem->put($filePathWithName, $file->getContent());
    }

    public function uploadTravelImage(UploadedFile $file, Travel $travel, ImageType $type): int
    {
        $path = $this->getPath($travel->id(), $file->getClientOriginalName());
        $this->uploadFiles($file, $path);

        return $this->repository->saveImage(0, [
            'travel_id'   => $travel->id(),
            'path'        => $path . '/' . $file->getClientOriginalName(),
            'size'        => $file->getSize(),
            'description' => null,
            'hash'        => hash_file('md5', $file->getPathname()),
            'user_id'     => $travel->getUser()->id,
            'type'        => $type->value,
        ]);
    }

    private function getPath(int $travelId, string $fileName): string
    {
        $basePath = self::TRAVEL_PATH . '/' . $travelId;

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
