<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\API\Response\Components;

final readonly class GroupComponent
{
    public function __construct(
        public int    $id,
        public string $name,
        public bool   $isVisible,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            isVisible: $data['isVisible'],
        );
    }
}
