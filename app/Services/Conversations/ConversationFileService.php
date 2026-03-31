<?php

declare(strict_types=1);

namespace App\Services\Conversations;

use App\Services\Upload\UploadBaseService;
use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use stdClass;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

final readonly class ConversationFileService extends UploadBaseService
{
    public function __construct(
        protected Filesystem                    $filesystem,
        private ConversationRepositoryInterface $repository,
        protected array                         $basePaths,
    ) {}

    public function validateFile(UploadedFile $file): void
    {
        if ($file->getSize() > 1000 * 1024 * 1024) {
            throw new InvalidArgumentException('File size exceeds the maximum allowed size of 100 MB.');
        }
    }

    private function uploadFile(UploadedFile $file, string $filePathWithName): void
    {
        $this->filesystem->put($filePathWithName, $file->getContent());
    }

    public function uploadConversationFile(string $messageId, UploadedFile $file, int $conversationId, int $userId): int
    {
        $hash = md5_file($file->getRealPath());

        $existsFile = $this->findExistsAttachment($conversationId, $hash);
        $filePathWithName = $existsFile?->path;
        $fileName = $file->getClientOriginalName();

        if (!$existsFile) {
            $path = $this->getPath($conversationId, $fileName);
            $filePathWithName = $path . '/' . $fileName;

            $this->uploadFile($file, $filePathWithName);
        }

        return $this->repository->addConversationMessageAttachment([
            'conversation_message_id' => $messageId,
            'path'                    => $filePathWithName,
            'hash'                    => $hash,
            'name'                    => $fileName,
            'mime_type'               => $file->getMimeType(),
            'size'                    => $file->getSize(),
            'user_id'                 => $userId,
            'conversation_id'         => $conversationId,
        ]);
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
        $existsFile = $this->repository->findExistsAttachment($file->conversation_id, $file->hash, $file->id);

        if ($existsFile) {
            return true;
        }

        return $this->deleteFile($file->path);
    }

    public function findExistsAttachment(int $conversationId, string $hash): ?stdClass
    {
        return $this->repository->findExistsAttachment($conversationId, $hash);
    }

    public function getMessageFileArchived(string $messageId, array $files): StreamedResponse
    {
        return response()->streamDownload(function () use ($files) {
            $zip = new ZipArchive();

            $tmpFile = tempnam(sys_get_temp_dir(), 'zip');

            if ($zip->open($tmpFile, ZipArchive::CREATE) !== TRUE) {
                throw new Exception('Cannot create zip');
            }

            foreach ($files as $file) {
                if (!Storage::disk($this->basePaths['disk'])->exists($file->path)) {
                    continue;
                }

                if ($this->filesystem->getAdapter() instanceof LocalFilesystemAdapter) {
                    $zip->addFile(Storage::disk($this->basePaths['disk'])->path($file->path), $file->name);
                } else {
                    $zip->addFromString($file->name, Storage::disk($this->basePaths['disk'])->get($file->path));
                }
            }

            $zip->close();

            readfile($tmpFile);
            unlink($tmpFile);
        }, "message_{$messageId}.zip");
    }

    public function getMessageFiles(string $messageId): array
    {
        return $this->repository->getMessageFiles($messageId);
    }
}