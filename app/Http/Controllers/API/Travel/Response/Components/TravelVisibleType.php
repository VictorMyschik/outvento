<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Response\Components;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TravelVisibleType',
    title: 'TravelVisibleType',
    description: 'Visibility type information for a travel item',
    required: ['key', 'name'],
    properties: [
        new OA\Property(property: 'key', description: 'Numeric visibility key', type: 'integer', format: 'int64', example: 1),
        new OA\Property(property: 'name', description: 'Human-readable visibility name', type: 'string', example: 'public'),
    ]
)]
final readonly class TravelVisibleType
{
    public function __construct(
        public int    $key,
        public string $name,
    ) {}
}
