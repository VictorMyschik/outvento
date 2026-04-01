<?php

declare(strict_types=1);

namespace App\Services\Conversations;

use App\Models\Conversations\Conversation;
use App\Models\User;
use App\Services\Conversations\Enum\Role;
use App\Services\Conversations\Enum\Type;
use Psr\Log\LoggerInterface;
use stdClass;

final readonly class ConversationService
{
    public function __construct(
        private ConversationRepositoryInterface $repository,
        private ConversationFileService         $uploadService,
        private LoggerInterface                 $log,
        private array                           $config,
    ) {}

    public function getConversationUsers(int $conversationId): array
    {
        return $this->repository->getConversationUsers($conversationId);
    }

    public function getConversationUserInfo(Conversation $conversation, User $user): stdClass
    {
        return $this->repository->getConversationUserInfo($conversation->id, $user->id);
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

    public function addMessage(int $conversationId, int $userId, ?string $text, array $files = []): void
    {
        $id = $this->repository->addMessage($conversationId, $userId, $text);

        $this->saveLinks($conversationId, $id, $userId, $text);

        foreach ($files as $file) {
            $this->uploadService->uploadConversationFile($id, $file, $conversationId, $userId);
        }
    }

    public function saveLinks(int $conversationId, string $messageId, int $userId, string $content): void
    {
        preg_match_all('/https?:\/\/[^\s]+/i', $content, $matches);

        $links = array_unique($matches[0] ?? []);

        $this->repository->saveLinks($conversationId, $messageId, $userId, $links);
    }

    public function updateMessage(int $conversationId, string $messageId, int $userId, ?string $content, array $files): void
    {
        $this->repository->updateMessage($messageId, $content);
        $this->saveLinks($conversationId, $messageId, $userId, $content);

        $message = $this->repository->getMessageById($messageId);

        foreach ($files as $file) {
            $this->uploadService->uploadConversationFile($messageId, $file, $message->conversation_id, $message->user_id);
        }
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

    public function clearHistoryUserConversation(int $conversationId, int $userId): void
    {
        $deletedIds = $this->repository->clearHistoryUserConversation($conversationId, $userId);

        foreach ($deletedIds as $deletedId) {
            $this->deleteAllMessageFiles($deletedId);
        }
    }

    public function restoreForUser(int $conversationId, int $userId): void
    {
        $this->repository->setConversationAsRestored($conversationId, $userId);
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

        $this->deleteAllMessageFiles($messageId);
        $this->repository->deleteMessageForUser($userId, $messageId);
    }

    public function deleteMessage(string $messageId): void
    {
        $this->deleteAllMessageFiles($messageId);
        $this->repository->deleteMessage($messageId);
    }

    public function deleteMessageFile(string $messageId, int $fileId): void
    {
        $file = $this->repository->getMessageFile($messageId, $fileId);

        $result = $this->uploadService->smartDeleteFile($file);

        if (!$result) {
            $this->log->error('Error deletion message file', ['messageId' => $messageId, 'path' => $file->path]);

            return;
        }

        $this->repository->deleteMessageFileModel($file->id);
    }

    public function deleteAllMessageFiles(string $messageId): void
    {
        $list = $this->repository->getMessageFiles($messageId);

        foreach ($list as $file) {
            $result = $this->uploadService->smartDeleteFile($file);

            if (!$result) {
                $this->log->error('Error deletion message file', ['messageId' => $messageId, 'path' => $file->path]);

                return;
            }

            $this->repository->deleteMessageFileModel($file->id);
        }
    }

    public function setMessageAsRead(int $conversationId, int $userId, string $messageId): void
    {
        $this->repository->setMessageAsRead($conversationId, $userId, $messageId);
    }

    public function renameMessageFile(int $fileId, string $name): void
    {
        $this->repository->renameMessageFile($fileId, $name);
    }

    public function getConversationAttachmentsSizeByUsers(int $conversationId): array
    {
        return $this->repository->getConversationAttachmentsSizeByUsers($conversationId);
    }

    public function validateAttachments(array $attachments): void
    {
        if (count($attachments) > $this->config['max_upload_files']) {
            throw new \InvalidArgumentException('You can upload a maximum of 10 attachments.');
        }

        foreach ($attachments as $attachment) {
            $this->uploadService->validateFile($attachment);
        }
    }

    public function deleteEmptyMessage(string $messageId): void
    {
        $message = $this->repository->getMessageById($messageId);

        if (empty($message->content)) {
            $files = $this->repository->getMessageFiles($messageId);

            if (empty($files)) {
                $this->deleteMessage($messageId);
            }
        }
    }
}