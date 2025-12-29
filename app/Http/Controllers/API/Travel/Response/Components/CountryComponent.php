<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Response\Components;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CountryComponent',
    title: 'CountryComponent',
    description: 'Country data returned by the API',
    required: ['id', 'name', 'continent'],
    properties: [
        new OA\Property(property: 'id', description: 'Identifier', type: 'integer', format: 'int64', example: 1),
        new OA\Property(property: 'name', description: 'Country name', type: 'string', example: 'France'),
        new OA\Property(property: 'continent', ref: '#/components/schemas/CountryContinentComponent', description: 'Continent information'),
    ]
)]
final readonly class CountryComponent
{
    public function __construct(
        public int                       $id,
        public string                    $name,
        public CountryContinentComponent $continent,
    ) {}
}
