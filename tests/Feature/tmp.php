<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class tmp
{
    protected static function randomIdFromClass(string $className): ?int
    {
        $object = new $className();
        $list = DB::table($object::TABLE)->limit(1000)->pluck('id')->toArray();

        return empty($list) ? null : $list[array_rand($list)];
    }

    protected static function randomString(?int $length = null, bool $upper = false): string
    {
        $length = $length ?: 50;

        $string = Str::random($length);

        if ($upper) {
            return mb_strtoupper($string);
        }

        return $string;
    }

    protected static function randomFloat(): string
    {
        return rand(1, 999) . '.' . rand(1, 9) . rand(1, 9);
    }
}
