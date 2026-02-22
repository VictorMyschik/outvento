<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum MediaType: int
{
    case Image = 0;
    case Video = 1;
    case File = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::Image => 'Image',
            self::Video => 'Video',
            self::File => 'File',
        };
    }
}
