<?php

declare(strict_types=1);

namespace App\Repositories\Constructor\Storage\Enum;

enum StorageFileTypeEnum: string
{
    case Video = '1';
    case SlideImage = '2';
}
