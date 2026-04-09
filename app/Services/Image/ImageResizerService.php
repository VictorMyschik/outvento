<?php

declare(strict_types=1);

namespace App\Services\Image;

use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

final readonly class ImageResizerService
{
    public function resize()
    {

        $file = __DIR__ . '/../../../tests/Feature/123.jpg';

        $manager = ImageManager::usingDriver(Driver::class);

        $image = $manager->decodePath($file);


        $image->orient(); // обязательно

        $image->scale(height: 300);

        $encoded = $image->encodeUsingFormat(Format::WEBP, quality: 65);

        $encoded->save(__DIR__ . '/1235.webp');
    }
}