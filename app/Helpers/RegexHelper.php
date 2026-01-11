<?php

declare(strict_types=1);

namespace App\Helpers;

final readonly class RegexHelper
{
    public const string EMAIL_REGEX = '/^\\w+([\\.-]?\\w+)*@\\w+([\\.-]?\\w+)*(\\.\\D{2,10})+$/';

    public const string SITE_WITH_URI = '/^(http(s)?://)?([\\w-]+\\.)+[\\w-]+(/[\\w- ;,./?%&=]*)?';
}
