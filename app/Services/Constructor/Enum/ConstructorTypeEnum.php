<?php

declare(strict_types=1);

namespace App\Services\Constructor\Enum;

enum ConstructorTypeEnum: string
{
    case Text = 'text';
    case Slider = 'slider';
    case Video = 'video';
    case OutVideo = 'out_video';
}
