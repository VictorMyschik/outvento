<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\API\Response\Components;

final readonly class CursorComponent
{
    public function __construct(
        public ?string $updatedAt,
        public int     $nmID,
        public int     $total,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['updatedAt'] ?? null,
            $data['nmID'],
            $data['total'],
        );
    }
}
