<?php

declare(strict_types=1);

namespace App\Services\Constructor\Enum;

enum ConstructorRelationType: string
{
    case News = 'news';
    case Articles = 'articles';
    case Blogs = 'blogs';
}