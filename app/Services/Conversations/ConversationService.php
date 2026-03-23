<?php

declare(strict_types=1);

namespace App\Services\Conversations;

use App\Models\Conversations\ConversationMessage;
use App\Services\Conversations\Enum\Type;

final readonly class ConversationService
{
    public function __construct(
        private ConversationRepositoryInterface $repository,
    ) {}

    public function addConversation(int $ownerId, int $userId, Type $type): int
    {
        $id = $this->repository->getConversationByUsers($ownerId, $userId);

        if (!$id) {
            return $this->repository->addConversation($ownerId, $userId, $type);
        }

        return $id;
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