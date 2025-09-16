<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\API\Response;

use App\Services\Catalog\Wildberries\API\Response\Components\GroupComponent;
use App\Services\Catalog\Wildberries\WBClientResponseInterface;

final readonly class ParentGroupsResponse implements WBClientResponseInterface
{
    /**
     * @param GroupComponent[] $data
     */
    public function __construct(
        public array $data, // GroupComponent[]
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            data: array_map(
                callback: fn(array $item) => GroupComponent::fromArray($item),
                array: $data['data'],
            ),
        );
    }
}
