<?php

declare(strict_types=1);

namespace App\Models\Upload;

use App\Models\ORM\ORM;
use App\Services\Upload\AttachmentTypeEnum;

class FileAttachment extends ORM
{
    public const null UPDATED_AT = null;

    protected $table = 'file_attachments';

    public $fillable = [
        'type',
        'object_id',
        'path',
        'name',
        'hash',
        'size',
        'extension',
        'user_id',
    ];

    public function getPath(): string
    {
        return $this->path;
    }

    public function getType(): AttachmentTypeEnum
    {
        return AttachmentTypeEnum::from($this->type);
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUrl(): string
    {
        return asset('storage' . $this->path);
    }
}