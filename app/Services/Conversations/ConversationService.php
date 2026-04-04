<?php

declare(strict_types=1);

namespace App\Services\Conversations;

use App\Models\Conversations\Conversation;
use App\Models\Conversations\ConversationMessage;
use App\Services\Conversations\DTO\GroupConversationDto;
use App\Services\Conversations\Enum\JoinPolicy;
use App\Services\Conversations\Enum\Role;
use App\Services\Conversations\Enum\Status;
use App\Services\Conversations\Enum\Type;
use Illuminate\Http\UploadedFile;
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

    public function checkAccess(int $conversationId, int $userId): void
    {
        $conversationUserInfo = $this->getConversationUserInfo($conversationId, $userId);

        if (!$conversationUserInfo || $conversationUserInfo->deleted_at) {
            throw new \InvalidArgumentException('Conversation not found');
        }
    }

    public function updateConversation(int $id, array $data): void
    {
        $this->repository->updateConversation($id, $data);
    }

    public function getConversationUsers(int $conversationId): array
    {
        return $this->repository->getConversationUsers($conversationId);
    }

    public function getConversationUserInfo(int $conversationId, int $userId): ?stdClass
    {
        return $this->repository->getConversationUserInfo($conversationId, $userId);
    }

    public function removeAvatar(Conversation $conversation): void
    {
        $result = $this->uploadService->deleteFile($conversation->avatar);

        if ($result) {
            $this->repository->updateConversation($conversation->id, ['avatar' => null]);
        }
    }

    public function addAvatar(int $conversationId, UploadedFile $file): void
    {
        $path = $this->uploadService->saveAvatar($conversationId, $file);
        $this->repository->updateConversation($conversationId, ['avatar' => $path]);
    }

    public function addToPinned(int $conversationId, string $messageId, int $userId): void
    {
        $this->repository->addToPinned($conversationId, $messageId, $userId);
    }

    public function deleteAllPinnedMessages(int $conversationId): void
    {
        $this->repository->deleteAllPinnedMessages($conversationId);
    }

    public function addGroupConversation(GroupConversationDto $dto): int
    {
        $id = $this->repository->addConversation($dto->type, $dto->title, $dto->joinPolicy);

        // Add Owner
        $this->repository->addUserToConversation($id, $dto->ownerId, Role::Owner, $dto->status);

        foreach ($dto->userIds as $userId) {
            if ((int)$userId === $dto->ownerId) {
                continue;
            }

            // Add users
            $this->repository->addUserToConversation($id, (int)$userId, Role::User, $dto->status);
        }

        return $id;
    }

    public function addUsersToGroupConversation(int $conversationId, array $userIds, Role $role, Status $status): void
    {
        foreach ($this->getConversationUsers($conversationId) as $user) {
            if (in_array($user->user_id, $userIds)) {
                unset($userIds[array_search($user->user_id, $userIds)]);
            }
        }

        foreach ($userIds as $userId) {
            $this->repository->addUserToConversation(
                conversationId: $conversationId,
                userId: (int)$userId,
                role: $role,
                status: $status,
            );
        }
    }

    public function updateUserConversation(int $conversationId, int $userId, Role $role, Status $status): void
    {
        $this->repository->updateConversationUser(
            conversationId: $conversationId,
            userId: (int)$userId,
            role: $role,
            status: $status,
        );
    }

    public function addPersonalConversation(int $ownerId, int $userId): int
    {
        $id = $this->repository->getPersonalConversationByUsers($ownerId, $userId);

        if (!$id) {
            $id = $this->repository->addConversation(Type::Private, null, JoinPolicy::Disable);

            $this->repository->addUserToConversation($id, $ownerId, Role::User, Status::Active);
            $this->repository->addUserToConversation($id, $userId, Role::User, Status::Active);
        }

        return $id;
    }

    public function setRole(int $conversationId, int $userId, Role $role): void
    {
        $this->repository->addUserToConversation($conversationId, $userId, $role, Status::Active);
    }

    public function addMessage(int $conversationId, int $userId, ?string $text, ?string $parentId, array $files = []): void
    {
        $id = $this->repository->addMessage($conversationId, $userId, $text, $parentId);

        $text && $this->saveLinks($conversationId, $id, $userId, $text);

        foreach ($files as $file) {
            $this->uploadService->uploadConversationFile($id, $file, $conversationId, $userId);
        }
    }

    public function saveLinks(int $conversationId, string $messageId, int $userId, ?string $content): void
    {
        if (empty($content)) {
            $this->repository->deleteLinksForMessage($conversationId, $messageId);

            return;
        }

        preg_match_all('/https?:\/\/[^\s]+/i', $content, $matches);

        $links = array_unique($matches[0] ?? []);

        $this->repository->saveLinks($conversationId, $messageId, $userId, $links);
    }

    public function getMessageById(int $userId, string $messageId): ?ConversationMessage
    {
        return $this->repository->getMessageById($userId, $messageId);
    }

    public function updateMessage(int $conversationId, string $messageId, int $userId, ?string $content, array $files): bool
    {
        $message = $this->getMessageById($userId, $messageId);

        if (!$message) {
            return false;
        }

        $this->repository->updateMessage($messageId, $content);
        $this->saveLinks($conversationId, $messageId, $userId, $content);

        foreach ($files as $file) {
            $this->uploadService->uploadConversationFile($messageId, $file, $message->conversation_id, $message->user_id);
        }

        return true;
    }

    public function getUnreadMessagesCount(int $conversationId, int $userId): int
    {
        return $this->repository->getUnreadMessagesCount($conversationId, $userId);
    }

    public function getLastMessageIdForUser(int $conversationId, int $userId): ?string
    {
        return $this->repository->getLastMessageIdForUser($conversationId, $userId);
    }

    public function restoreForUser(int $conversationId, int $userId): void
    {
        $this->repository->setConversationAsRestored($conversationId, $userId);
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

    #region Delete
    public function removeForUser(int $conversationId, int $userId): void
    {
        $this->repository->setConversationUserAsDeleted($conversationId, $userId);
    }

    public function setConversationDeleted(int $conversationId): void
    {
        $this->repository->setConversationDeleted($conversationId);
    }

    public function clearHistoryUserConversation(int $conversationId, int $userId): void
    {
        $this->repository->clearHistoryUserConversation($conversationId, $userId);
    }

    public function deleteRemovedMessages(): void
    {
        foreach ($this->repository->getRemovedMessageIds(10) as $id) {
            $this->deleteMessageHard($id);
        }

        foreach ($this->repository->getDeletedConversationIds(1) as $conversationId) {
            foreach ($this->repository->getMessages($conversationId) as $messageId) {
                $this->deleteMessageHard($messageId);
            }

            $this->repository->deleteConversation($conversationId);
        }
    }

    public function deleteMessageForUser(int $conversationId, int $userId, string $messageId): void
    {
        $deletedByCount = $this->repository->getDeletedByCount($messageId);
        $conversationUsers = $this->repository->getConversationUsersByConversationId($conversationId);

        if (($deletedByCount + 1) === count($conversationUsers)) {
            $this->deleteMessageHard($messageId);

            return;
        }

        $this->repository->deleteMessageForUser($userId, $messageId);
    }

    public function deleteMessageHard(string $messageId): void
    {
        $this->deleteAllMessageFiles($messageId);
        $this->repository->deleteMessageHard($messageId);
    }

    public function deleteMessageFile(string $messageId, int $fileId): void
    {
        $file = $this->repository->getMessageFile($messageId, $fileId);

        if (!$file) {
            return;
        }

        $result = $this->uploadService->smartDeleteFile($file);

        if (!$result) {
            $this->log->error('Error deletion message file', ['messageId' => $messageId, 'path' => $file->path]);

            return;
        }

        $this->repository->deleteMessageFileModel($file->id);

        $this->deleteEmptyMessage($messageId);
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

    public function deleteEmptyMessage(string $messageId): void
    {
        $message = $this->repository->getMessageById(null, $messageId);

        if (empty($message->content)) {
            $files = $this->repository->getMessageFiles($messageId);

            if (empty($files)) {
                $this->deleteMessageHard($messageId);
            }
        }
    }
    #endregion
}