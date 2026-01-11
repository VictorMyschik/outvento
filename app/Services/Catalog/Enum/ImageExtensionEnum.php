<?php

namespace App\Services\Catalog\Enum;

use Exception;

enum ImageExtensionEnum: string
{
    case IMAGE_PJPEG = 'image/pjpeg';
    case IMAGE_JPEG = 'image/jpeg';
    case IMAGE_X_PNG = 'image/x-png';
    case IMAGE_PNG = 'image/png';
    case IMAGE_GIF = 'image/gif';
    case IMAGE_SVG_XML = 'image/svg+xml';

    public function getImageExtensionByType(): string
    {
        return match ($this) {
            ImageExtensionEnum::IMAGE_PJPEG => '.pjpeg',
            ImageExtensionEnum::IMAGE_JPEG => '.jpeg',
            ImageExtensionEnum::IMAGE_X_PNG => '.x-png',
            ImageExtensionEnum::IMAGE_PNG => '.png',
            ImageExtensionEnum::IMAGE_GIF => '.gif',
            ImageExtensionEnum::IMAGE_SVG_XML => '.svg',
            default => throw new Exception('Unknown image type'),
        };
    }

    public static function getList(): array
    {
        return [
            self::IMAGE_PJPEG->value,
            self::IMAGE_JPEG->value,
            self::IMAGE_X_PNG->value,
            self::IMAGE_PNG->value,
            self::IMAGE_GIF->value,
            self::IMAGE_SVG_XML->value,
        ];
    }
}
