<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\API\Response;


use App\Services\Catalog\Wildberries\API\Response\Components\ChildGroupComponent;
use App\Services\Catalog\Wildberries\WBClientResponseInterface;

final readonly class ChildGroupsResponse implements WBClientResponseInterface
{
    /**
     * @param ChildGroupComponent[] $data
     */
    public function __construct(
        public array $data, // ChildGroupComponent[]
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            data: array_map(
                callback: fn(array $item) => ChildGroupComponent::fromArray($item),
                array: $data['data'],
            ),
        );
    }
}
