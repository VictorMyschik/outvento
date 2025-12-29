<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Response\Components;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TravelUserComponent',
    title: 'TravelUserComponent',
    description: 'User data for travel responses',
    required: ['name', 'email'],
    properties: [
        new OA\Property(property: 'name', description: 'User display name', type: 'string', example: 'john_doe'),
        new OA\Property(property: 'email', description: 'User email address', type: 'string', format: 'email', example: 'john@example.com'),
    ]
)]
final readonly class TravelUserComponent
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}
}
