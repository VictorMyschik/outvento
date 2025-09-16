<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\API\Response;


use App\Services\Catalog\Wildberries\API\Response\Components\AttributeComponent;
use App\Services\Catalog\Wildberries\WBClientResponseInterface;

final readonly class AttributesResponse implements WBClientResponseInterface
{
    public function __construct(
        public array $data, // AttributeComponent[]
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            data: array_map(
                callback: fn(array $item) => AttributeComponent::fromArray($item),
                array: $data['data'],
            ),
        );
    }
}
