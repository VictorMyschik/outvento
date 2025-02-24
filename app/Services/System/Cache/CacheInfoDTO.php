<?php

declare(strict_types=1);

namespace App\Services\System\Cache;

class CacheInfoDTO
{
    public function __construct(
        public string $name,
        public string $version,
        public string $memory_full,
        public int    $objects_in_cache,
        public string $current_memory,
        public int    $current_db
    ) {}
}
