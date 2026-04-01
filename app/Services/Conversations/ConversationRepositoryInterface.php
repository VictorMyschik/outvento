<?php

declare(strict_types=1);

namespace App\Services\Conversations;

use App\Models\Conversations\Conversation;
use App\Models\Conversations\ConversationMessage;
use App\Services\Conversations\Enum\Role;
use App\Services\Conversations\Enum\Type;
use stdClass;

interface ConversationRepositoryInterface
{
    public function getConversationUserInfo(int $conversationId, int $userId): stdClass;

    public function getConversationUsers(int $conversationId): array;

    public function getPersonalConversationByUsers(int $ownerId, int $userId): ?int;

    public function getConversationUsersByConversationId(int $conversationId): array;

    public function getDeletedByCount(string $messageId): int;

    public function getConversationById(int $conversationId): ?Conversation;

    public function addConversation(Type $type, ?string $title): int;

    public function addUserToConversation(int $conversationId, int $userId, Role $role): void;

    public function addMessage(int $conversationId, int $userId, ?string $text): string;

    public function saveLinks(int $conversationId, string $messageId, int $userId, array $links): void;

    public function updateMessage(string $messageId, ?string $content): void;

    public function getUnreadMessagesCount(int $conversationId, int $userId): int;

    public function setConversationUserAsDeleted(?int $conversationId, int $userId): void;

    public function setConversationAsRestored(int $conversationId, int $userId): void;

    public function getRemovedConversationIds(): array;

    public function deleteMessageForUser(int $userId, string $messageId): void;

    public function deleteMessage(string $messageId): void;

    public function setMessageAsRead(int $conversationId, int $userId, string $messageId): void;

    public function renameMessageFile(int $fileId, string $name): void;

    public function getLastMessageIdForUser(int $conversationId, int $userId): ?string;

    public function getConversationAttachmentsSizeByUsers(int $conversationId): array;

    public function addConversationMessageAttachment(array $data): int;

    public function findExistsAttachment(int $conversationId, string $hash, ?int $ignoreId = null): ?stdClass;

    public function getMessageFiles(string $messageId): array;

    public function getMessageFile(string $messageId, int $fileId): ?stdClass;

    public function deleteMessageFileModel(int $fileId): void;

    public function getMessageById(string $messageId): ?ConversationMessage;

    public function clearHistoryUserConversation(int $conversationId, int $userId): array;
}