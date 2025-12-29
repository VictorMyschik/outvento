<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Response\Components;

final readonly class MembersComponent
{
    public function __construct(
        public ?int $maxMember,
        public ?int $existsMembers,
        public string $title,
        public string $icon = '/storage/images/icons/people-group.png',
    ) {}
}
