<?php

declare(strict_types=1);

namespace App\Repositories\UploadService;

use App\Models\Upload\FileAttachment;
use App\Repositories\DatabaseRepository;

final readonly class UploadServiceDBRepository extends DatabaseRepository
{
    public function addFile(array $data): int
    {
        return $this->db->table(FileAttachment::getTableName())->insertGetId($data);
    }

    public function deleteAttachment(int $id): void
    {
        $this->db->table(FileAttachment::getTableName())->where('id', $id)->delete();
    }
}