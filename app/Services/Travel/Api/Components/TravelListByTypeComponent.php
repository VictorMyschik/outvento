<?php

declare(strict_types=1);

namespace App\Services\Travel\Api\Components;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TravelListByTypeComponent',
    title: 'TravelListByTypeComponent',
    description: 'List of travels grouped by travel type',
    required: ['travelTypeId', 'travels'],
    properties: [
        new OA\Property(
            property: 'travelTypeId',
            description: 'Travel type identifier (TravelTypeComponent id)',
            type: 'integer',
            format: 'int64',
            example: 1
        ),
        new OA\Property(
            property: 'travels',
            description: 'Array of TravelComponent objects',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TravelDetailsResponse')
        ),
    ]
)]
final readonly class TravelListByTypeComponent
{
    public function __construct(
        public int   $travelTypeId, // TravelTypeComponent id
        public array $travels, // TravelDetailsResponse[]
    ) {}
}