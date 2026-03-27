<?php

declare(strict_types=1);

namespace App\Services\Conversations;

use App\Services\Conversations\Enum\Role;
use App\Services\Conversations\Enum\Type;

final readonly class ConversationService
{
    public function __construct(
        private ConversationRepositoryInterface $repository,
    ) {}

    public function getConversationUsers(int $conversationId): array
    {
        return $this->repository->getConversationUsers($conversationId);
    }

    public function addGroupConversation(int $ownerId, array $userIds, string $title): int
    {
        $id = $this->repository->addConversation(Type::Group, $title);

        $this->repository->addUserToConversation($id, $ownerId, Role::Admin);

        foreach ($userIds as $userId) {
            if ((int)$userId === $ownerId) {
                continue;
            }
            $this->repository->addUserToConversation($id, (int)$userId, Role::User);
        }

        return $id;
    }

    public function addUserToGroupConversation(int $conversationId, int $userId, Role $role): void
    {
        $this->repository->addUserToConversation($conversationId, $userId, $role);
    }

    public function addPersonalConversation(int $ownerId, int $userId): int
    {
        $id = $this->repository->getPersonalConversationByUsers($ownerId, $userId);

        if (!$id) {
            $id = $this->repository->addConversation(Type::Private, null);

            $this->repository->addUserToConversation($id, $ownerId, Role::User);
            $this->repository->addUserToConversation($id, $userId, Role::User);
        }

        return $id;
    }

    public function setRole(int $conversationId, int $userId, Role $role): void
    {
        $this->repository->addUserToConversation($conversationId, $userId, $role);
    }

    public function addMessage(int $conversationId, int $userId, string $text): void
    {
        $this->repository->addMessage($conversationId, $userId, $text);
    }

    public function updateMessage(string $messageId, string $content): void
    {
        $this->repository->updateMessage($messageId, $content);
    }

    public function getUnreadMessagesCount(int $conversationId, int $userId): int
    {
        return $this->repository->getUnreadMessagesCount($conversationId, $userId);
    }

    public function getLastMessageIdForUser(int $conversationId, int $userId): ?string
    {
        return $this->repository->getLastMessageIdForUser($conversationId, $userId);
    }

    public function purgeConversation(int $conversationId): void
    {
        $this->repository->purgeConversation($conversationId);
    }

    public function removeForUser(?int $conversationId, int $userId): void
    {
        $this->repository->setConversationAsDeleted($conversationId, $userId);
    }

    public function deleteRemovedMessages(): void
    {
        // Delete full conversations for users who have removed them
        foreach ($this->repository->getRemovedConversationIds() as $conversationId) {
            $this->deleteMessagesByConversationId($conversationId);
        }
    }

    public function deleteMessagesByConversationId(int $conversationId): void
    {
        $this->repository->deleteMessagesByConversationId($conversationId);
    }

    public function deleteMessageForUser(int $conversationId, int $userId, string $messageId): void
    {
        $deletedByCount = $this->repository->getDeletedByCount($messageId);
        $conversationUsers = $this->repository->getConversationUsersByConversationId($conversationId);

        if (($deletedByCount + 1) === count($conversationUsers)) {
            $this->deleteMessage($messageId);

            return;
        }

        $this->repository->deleteMessageForUser($userId, $messageId);
    }

    public function deleteMessage(string $messageId): void
    {
        $this->repository->deleteMessage($messageId);
    }

    public function setMessageAsRead(int $conversationId, int $userId, string $messageId): void
    {
        $this->repository->setMessageAsRead($conversationId, $userId, $messageId);
    }
}