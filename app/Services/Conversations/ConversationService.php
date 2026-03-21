<?php

declare(strict_types=1);

namespace App\Services\Conversations;

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
        foreach ($this->repository->getRemovedConversationIds() as $conversationId) {
            $this->deleteMessagesByConversationId($conversationId);
        }
    }

    public function deleteMessagesByConversationId(int $conversationId): void
    {
        $this->repository->deleteMessagesByConversationId($conversationId);
    }
}