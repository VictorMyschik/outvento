<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Response;

use App\Http\Controllers\API\Travel\Response\Components\MembersComponent;
use App\Http\Controllers\API\Travel\Response\Components\TravelStatusComponent;
use App\Http\Controllers\API\Travel\Response\Components\TravelUserComponent;
use App\Http\Controllers\API\Travel\Response\Components\TravelVisibleType;
use App\Services\References\API\Response\Components\CountryComponent;
use App\Services\Travel\Api\Components\TravelTypeComponent;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TravelDetailsResponse',
    title: 'TravelDetailsResponse',
    description: 'Detailed travel information returned by the API',
    required: ['id', 'title', 'status', 'visibleType', 'user', 'country', 'travelType', 'dateFrom', 'members', 'images', 'owner'],
    properties: [
        new OA\Property(property: 'id', description: 'Identifier', type: 'integer', format: 'int64', example: 123),
        new OA\Property(property: 'title', description: 'Travel title', type: 'string', example: 'Mountain Hike'),
        new OA\Property(property: 'preview', description: 'Preview image URL', type: 'string', format: 'uri', example: 'https://example.com/preview.jpg', nullable: true),
        new OA\Property(property: 'description', description: 'Detailed description', type: 'string', nullable: true),
        new OA\Property(property: 'status', ref: '#/components/schemas/TravelStatusComponent', description: 'Travel status'),
        new OA\Property(property: 'visibleType', ref: '#/components/schemas/TravelVisibleType', description: 'Visibility type'),
        new OA\Property(property: 'user', ref: '#/components/schemas/TravelUserComponent', description: 'Owner user data'),
        new OA\Property(property: 'country', ref: '#/components/schemas/CountryComponent', description: 'Country information'),
        new OA\Property(property: 'travelType', ref: '#/components/schemas/TravelTypeComponent', description: 'Type of travel'),
        new OA\Property(property: 'dateFrom', description: 'Start date (ISO 8601)', type: 'string', format: 'date', example: '2025-06-01'),
        new OA\Property(property: 'dateTo', description: 'End date (ISO 8601)', type: 'string', format: 'date', example: '2025-06-05', nullable: true),
        new OA\Property(property: 'members', ref: '#/components/schemas/MembersComponent', description: 'Members information'),
        new OA\Property(property: 'images', description: 'Array of images', type: 'array', items: new OA\Items(ref: '#/components/schemas/TravelMediaComponent')),
        new OA\Property(property: 'owner', description: 'Owner name or identifier', type: 'string', example: 'john_doe'),
    ]
)]
final readonly class TravelDetailsResponse
{
    public function __construct(
        public int                   $id,
        public string                $title,
        public ?string               $preview,
        public ?string               $description,
        public TravelStatusComponent $status,
        public TravelVisibleType     $visibleType,
        public TravelUserComponent   $user,
        public CountryComponent      $country,
        public TravelTypeComponent   $travelType,
        public string                $dateFrom,
        public ?string               $dateTo,
        public MembersComponent      $members,
        public array                 $images, // TravelMediaComponent[]
        public string                $owner,
    ) {}
}
