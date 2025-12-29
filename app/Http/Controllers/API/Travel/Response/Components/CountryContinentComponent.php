<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Response\Components;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CountryContinentComponent',
    title: 'CountryContinentComponent',
    description: 'Continent information for a country',
    required: ['name', 'shortName'],
    properties: [
        new OA\Property(property: 'name', description: 'Continent full name', type: 'string', example: 'Europe'),
        new OA\Property(property: 'shortName', description: 'Continent short code or abbreviation', type: 'string', example: 'EU'),
    ]
)]
final readonly class CountryContinentComponent
{
    public function __construct(
        public string $name,
        public string $shortName,
    ) {}
}
