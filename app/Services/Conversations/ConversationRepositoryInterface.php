<?php

declare(strict_types=1);

namespace App\Services\Conversations;

use App\Models\Conversations\Conversation;
use App\Services\Conversations\Enum\Role;
use App\Services\Conversations\Enum\Type;

interface ConversationRepositoryInterface
{
    public function getPersonalConversationByUsers(int $ownerId, int $userId): ?int;

    public function getConversationUsersByConversationId(int $conversationId): array;

    public function getDeletedByCount(string $messageId): int;

    public function getConversationById(int $conversationId): ?Conversation;

    public function addConversation(Type $type, ?string $title): int;

    public function addUserToConversation(int $conversationId, int $userId, Role $role): void;

    public function addMessage(int $conversationId, int $userId, string $text): void;

    public function updateMessage(string $messageId, string $content): void;

    public function getUnreadMessagesCount(int $conversationId, int $userId): int;

    public function purgeConversation(int $conversationId): void;

    public function setConversationAsDeleted(?int $conversationId, int $userId): void;

    public function getRemovedConversationIds(): array;

    public function deleteMessagesByConversationId(int $conversationId): void;

    public function deleteMessageForUser(int $userId, string $messageId): void;

    public function deleteMessage(string $messageId): void;

    public function setMessageAsRead(int $conversationId, int $userId, string $messageId): void;

    public function getLastMessageIdForUser(int $conversationId, int $userId): ?string;
}