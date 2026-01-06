<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Response\Components;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MembersComponent',
    title: 'MembersComponent',
    description: 'Membership details for a travel item',
    required: ['title'],
    properties: [
        new OA\Property(property: 'maxMember', description: 'Maximum allowed members', type: 'integer', format: 'int64', example: 10, nullable: true),
        new OA\Property(property: 'existsMembers', description: 'Currently existing members count', type: 'integer', format: 'int64', example: 3, nullable: true),
        new OA\Property(property: 'title', description: 'Title for the members block', type: 'string', example: 'Participants'),
        new OA\Property(property: 'icon', description: 'Icon path for the members block', type: 'string', example: '/storage/images/icons/people-group.png'),
    ]
)]
final readonly class MembersComponent
{
    public function __construct(
        public ?int   $maxMember,
        public ?int   $existsMembers,
        public string $title,
        public string $icon = '/storage/images/icons/people-group.png',
    ) {}
}
