<?php

declare(strict_types=1);

namespace App\Services\System\Cache;

use Illuminate\Support\Facades\Redis;
use RedisException;

class CacheRedisClass
{
    public string $name = 'Redis';

    public function __construct(private readonly Redis $client)
    {
        $this->client::select(1);
    }

    public function getDisplayInfo(): CacheInfoDTO
    {
        return new CacheInfoDTO(
            $this->name,
            $this->getVersion(),
            $this->getAllowedMemory(),
            $this->getObjectsCount(),
            $this->getUsedMemory(),
            $this->client::getDbNum(),
        );
    }

    /**
     * @return false|array|Redis
     * @throws RedisException
     */
    public function getRawInfo(): mixed
    {
        return $this->client::info();
    }

    private function getUsedMemory(): string
    {
        $memoryRSS = round(100 * ($this->client::info()['used_memory_rss'] / 1024 / 1024) / (5 * 1024), 2);

        return $this->client::info()['used_memory_rss_human'] . ' / ' . $memoryRSS . '%';
    }

    private function getVersion(): string
    {
        return $this->client::info()['redis_version'];
    }

    private function getAllowedMemory(): string
    {
        return $this->client::info()['total_system_memory_human'];
    }

    private function getObjectsCount(): int
    {
        return $this->client::dbSize();
    }
}
