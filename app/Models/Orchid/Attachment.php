<?php

declare(strict_types=1);

namespace App\Models\Orchid;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\ORM\ORM;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Attachment extends ORM
{
    use NameFieldTrait;
    use DescriptionNullableFieldTrait;

    protected $table = 'attachments';
    public ?UploadedFile $file = null;
    protected $fillable = [
        'name',
        'original_name',
        'mime',
        'extension',
        'size',
        'sort',
        'path',
        'description',
        'alt',
        'hash',
        'disk',
        'user_id',
        'group',
    ];

    public function beforeDelete(): void
    {
        Storage::delete($this->getFullPath());
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getOriginalName(): string
    {
        return $this->original_name;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getFullPath(): string
    {
        return $this->getPath() . $this->getName() . '.' . $this->getExtension();
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }
}
