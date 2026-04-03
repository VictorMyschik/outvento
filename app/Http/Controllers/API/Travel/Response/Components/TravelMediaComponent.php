<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Response\Components;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TravelMediaComponent',
    title: 'TravelMediaComponent',
    description: 'Image information for a travel item',
    required: ['logo', 'url'],
    properties: [
        new OA\Property(property: 'logo', description: 'Whether this image is the logo', type: 'boolean', example: true),
        new OA\Property(property: 'url', description: 'Image URL', type: 'string', format: 'uri', example: 'https://example.com/image.jpg'),
        new OA\Property(property: 'description', description: 'Optional image description', type: 'string', example: 'Front view of the hiking trail', nullable: true),
    ]
)]
final readonly class TravelMediaComponent
{
    public function __construct(
        public bool    $logo,
        public string  $url,
        public ?string $description,
    ) {}
}
