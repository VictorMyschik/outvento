<?php

declare(strict_types=1);

namespace App\Services\Upload;

use App\Models\Upload\FileAttachment;
use App\Repositories\UploadService\UploadServiceDBRepository;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;

final readonly class UploadService
{
    public function __construct(
        private Filesystem                $filesystem,
        private UploadServiceDBRepository $repository,
    ) {}

    public function uploadAttachment(string $path, UploadedFile $file): string
    {
        $filename = md5_file($file->getRealPath()) . '.' . $file->extension();
        $this->filesystem->putFileAs($path, $file, $filename);

        return $path . '/' . $filename;
    }

    public function uploadAttachmentWithOriginalName(string $path, UploadedFile $file): string
    {
        $path = $this->getNewFilePath($path, $file->getClientOriginalName());
        $filePathWithName = $path . '/' . $file->getClientOriginalName();

        $this->filesystem->put($filePathWithName, $file->getContent());

        return $filePathWithName;
    }

    public function deleteFile(string $path): void
    {
        $this->filesystem->delete($path);
    }

    private function getNewFilePath(string $baseDir, string $fileName): string
    {
        $list = array_reverse($this->filesystem->directories($baseDir));

        foreach ($list as $dir) {
            if ($this->filesystem->exists($dir . '/' . $fileName) || count($this->filesystem->files($dir)) >= 50) {
                continue;
            }

            return '/' . $dir;
        }

        $currentDir = count($list) + 1;

        return $baseDir . '/' . $currentDir;
    }

    public function deleteDirectory(string $path): void
    {
        $this->filesystem->deleteDirectory($path);
    }

    public function addAttachment(AttachmentTypeEnum $type, string $path, UploadedFile $file, int $objectId, int $userId): int
    {
        $data = [
            'type'      => $type->value,
            'path'      => $path,
            'hash'      => md5_file($file->getRealPath()),
            'size'      => $file->getSize(),
            'object_id' => $objectId,
            'name'      => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'user_id'   => $userId,
        ];

        return $this->repository->addAttachment($data);
    }

    public function deleteAttachment(FileAttachment $attachment): void
    {
        $this->deleteFile($attachment->getPath());
        $this->repository->deleteAttachment($attachment->id());
    }
}
