<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Response\Components;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TravelStatusComponent',
    title: 'TravelStatusComponent',
    description: 'Status information for a travel item',
    required: ['key', 'name'],
    properties: [
        new OA\Property(property: 'key', description: 'Numeric status key', type: 'integer', format: 'int64', example: 1),
        new OA\Property(property: 'name', description: 'Human-readable status name', type: 'string', example: 'active'),
    ]
)]
final readonly class TravelStatusComponent
{
    public function __construct(
        public int    $key,
        public string $name,
    ) {}
}
