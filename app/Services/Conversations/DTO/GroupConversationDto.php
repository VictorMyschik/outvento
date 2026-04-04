<?php

declare(strict_types=1);

namespace App\Services\Conversations\DTO;

use App\Services\Conversations\Enum\JoinPolicy;
use App\Services\Conversations\Enum\Status;
use App\Services\Conversations\Enum\Type;

final readonly class GroupConversationDto
{
    public function __construct(
        public int        $ownerId,
        public array      $userIds,
        public string     $title,
        public Type       $type,
        public JoinPolicy $joinPolicy,
        public Status     $status,
    ) {}
}