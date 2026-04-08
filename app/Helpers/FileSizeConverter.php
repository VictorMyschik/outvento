<?php

declare(strict_types=1);

namespace App\Helpers;

final readonly class FileSizeConverter
{
    const string Kb = 'Kb';
    const string Mb = 'Mb';
    const string Gb = 'Gb';

    public static function bytesTo(int $bites, string $measure = self::Mb, int $round = 2): float
    {
        if ($bites < 0) {
            throw new \InvalidArgumentException('Bytes must be a non-negative integer.');
        }

        $normalized = ucfirst(strtolower($measure));

        $result = match ($normalized) {
            self::Kb => $bites / 1024,
            self::Mb => $bites / (1024 ** 2),
            self::Gb => $bites / (1024 ** 3),
            default => throw new \InvalidArgumentException(sprintf('Unknown measure "%s". Allowed: %s, %s, %s.', $measure, self::Kb, self::Mb, self::Gb)),
        };

        return round($result, $round);
    }
}