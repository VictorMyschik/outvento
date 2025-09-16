<?php

declare(strict_types=1);

namespace App\Services\System\Supervisor;

final readonly class SupervisorService
{
    public function __construct(private Client $client) {}

    public function stopAllWorkers(): void
    {
        $this->client->stopAllWorkers();
    }

    public function startAllWorkers(): void
    {
        $this->client->startAllWorkers();
    }

    public function getList(): array
    {
        return $this->client->getWorkerList();
    }

    public function startGroupWorkers(string $groupName): void
    {
        $this->client->startGroupWorkers($groupName);
    }

    public function stopGroupWorkers(string $groupName): void
    {
        $this->client->stopGroupWorkers($groupName);
    }
}