<?php

declare(strict_types=1);

namespace App\Services\Conversations;

use App\Services\Conversations\Enum\Type;

interface ConversationRepositoryInterface
{
    public function getConversationByUsers(int $ownerId, int $userId): ?int;

    public function addConversation(int $ownerId, int $userId, Type $type): int;

    public function addMessage(int $conversationId, int $userId, string $text): void;

    public function purgeConversation(int $conversationId): void;

    public function setConversationAsDeleted(?int $conversationId, int $userId): void;

    public function getRemovedConversationIds(): array;

    public function deleteMessagesByConversationId(int $conversationId): void;
}