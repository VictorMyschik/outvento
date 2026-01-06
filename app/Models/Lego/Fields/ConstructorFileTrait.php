<?php

declare(strict_types=1);

namespace App\Models\Lego\Fields;

use App\Models\Constructor\ConstructorFile;

trait ConstructorFileTrait
{
    public function getFile(): ConstructorFile
    {
        return $this->hasOne(ConstructorFile::class, 'id', 'file_id')->firstOrFail();
    }
}