<?php

declare(strict_types=1);

namespace App\Models\Constructor;

use App\Models\ORM\ORM;
use App\Models\User;

class Video extends ORM
{
    protected $table = 'videos';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function setFileName(string $value): void
    {
        $this->file_name = $value;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $value): void
    {
        $this->path = $value;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $value): void
    {
        $this->size = $value;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $value): void
    {
        $this->extension = $value;
    }

    public function getUser(): User
    {
        return User::find($this->user_id);
    }

    public function setUser(int $value): void
    {
        $this->user_id = $value;
    }

    public function getFilePathWithName(): string
    {
        return $this->getPath() . '/' . $this->getFileName();
    }
}