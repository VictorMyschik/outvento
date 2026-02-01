<?php

declare(strict_types=1);

namespace App\Services\Other\Faq;

final readonly class FaqService
{
    public function __construct(
        private FaqRepositoryInterface $repository,
    ) {}

    public function saveFaq(int $id, array $data): int
    {
        return $this->repository->saveFaq($id, $data);
    }
}