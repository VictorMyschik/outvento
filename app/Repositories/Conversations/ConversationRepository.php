<?php

declare(strict_types=1);

namespace App\Repositories\Conversations;

use App\Models\Conversations\Conversation;
use App\Models\Conversations\ConversationMessage;
use App\Models\Conversations\ConversationMessageAttachment;
use App\Models\Conversations\ConversationMessageLink;
use App\Models\Conversations\ConversationMessageUserState;
use App\Models\Conversations\ConversationUser;
use App\Models\User;
use App\Repositories\DatabaseRepository;
use App\Services\Conversations\ConversationRepositoryInterface;
use App\Services\Conversations\Enum\Role;
use App\Services\Conversations\Enum\Type;
use stdClass;

final readonly class ConversationRepository extends DatabaseRepository implements ConversationRepositoryInterface
{
    public function getConversationById(int $conversationId): ?Conversation
    {
        return Conversation::loadBy($conversationId);
    }

    public function getPersonalConversationByUsers(int $ownerId, int $userId): ?int
    {
        $count = $ownerId === $userId ? 1 : 2;

        return $this->db->table(ConversationUser::TABLE)
            ->join(Conversation::TABLE, function ($query) use ($ownerId, $userId) {
                $query->on(ConversationUser::TABLE . '.conversation_id', '=', Conversation::TABLE . '.id')
                    ->where('type', Type::Private->value);
            })
            ->whereIn('user_id', [$ownerId, $userId])
            ->groupBy('conversation_id')
            ->havingRaw('count(conversation_id) > ' . $count)
            ->value('conversation_id');
    }

    public function addConversation(Type $type, ?string $title): int
    {
        return $this->db->table(Conversation::getTableName())->insertGetId([
            'type'  => $type->value,
            'title' => $title,
        ]);
    }

    public function addUserToConversation(int $conversationId, int $userId, Role $role): void
    {
        $this->db->table(ConversationUser::TABLE)->updateOrInsert([
            'conversation_id' => $conversationId,
            'user_id'         => $userId,
        ], [
            'role' => $role->value
        ]);
    }

    public function addMessage(int $conversationId, int $userId, ?string $text): string
    {
        $id = $this->newUlidId();

        $this->db->table(ConversationMessage::TABLE)->insert([
            'id'              => $id,
            'conversation_id' => $conversationId,
            'user_id'         => $userId,
            'content'         => $text,
        ]);

        $this->db->table(ConversationUser::TABLE)
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->update(['last_read_message_id' => $id]);

        return $id;
    }

    public function setConversationUserAsDeleted(?int $conversationId, int $userId): void
    {
        $this->db->table(ConversationUser::TABLE)
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->update(['deleted_at' => now()]);
    }

    public function getRemovedConversationIds(): array
    {
        return $this->db->table(ConversationUser::TABLE)
            ->select('conversation_id')
            ->groupBy('conversation_id')
            ->havingRaw('COUNT(*) = COUNT(deleted_at)')
            ->pluck('conversation_id')
            ->toArray();
    }

    public function deleteMessageForUser(int $userId, string $messageId): void
    {
        $this->db->beginTransaction();

        $this->db->table(ConversationMessageUserState::TABLE)->updateOrInsert(['message_id' => $messageId, 'user_id' => $userId]);
        $this->db->table(ConversationMessage::TABLE)
            ->where('id', $messageId)
            ->update([
                'deleted_by_count' => $this->db->raw('deleted_by_count + 1'),
            ]);

        $this->db->commit();
    }

    public function updateMessage(string $messageId, ?string $content): void
    {
        $this->db->table(ConversationMessage::TABLE)
            ->where('id', $messageId)
            ->update(['content' => $content, 'edited_at' => now()]);
    }

    public function deleteMessage(string $messageId): void
    {
        $this->db->table(ConversationMessageUserState::TABLE)->where('message_id', $messageId)->delete();
        $this->db->table(ConversationMessage::TABLE)->where('id', $messageId)->delete();
    }

    public function getConversationUsersByConversationId(int $conversationId): array
    {
        return $this->db->table(ConversationUser::TABLE)->where(['conversation_id' => $conversationId])->get()->all();
    }

    public function getDeletedByCount(string $messageId): int
    {
        return $this->db->table(ConversationMessage::TABLE)->where('id', $messageId)->value('deleted_by_count');
    }

    public function getUnreadMessagesCount(int $conversationId, int $userId): int
    {
        return $this->db->table(ConversationMessage::TABLE . ' as m')
            ->join(ConversationUser::TABLE . ' as c', function ($join) use ($userId) {
                $join->on('c.conversation_id', '=', 'm.conversation_id')
                    ->where('c.user_id', '=', $userId);
            })
            ->leftJoin(ConversationMessageUserState::TABLE . ' as s', function ($join) use ($userId) {
                $join->on('m.id', '=', 's.message_id')
                    ->where('s.user_id', '=', $userId);
            })
            ->where('m.conversation_id', $conversationId)

            // ❗ не считаем удалённые пользователем
            ->whereNull('s.updated_at')

            // ❗ unread логика
            ->where(function ($q) {
                $q->whereNull('c.last_read_message_id')
                    ->orWhereColumn('m.id', '>', 'c.last_read_message_id');
            })
            ->count();
    }

    public function setMessageAsRead(int $conversationId, int $userId, string $messageId): void
    {
        $this->db->table(ConversationUser::TABLE)
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->update(['last_read_message_id' => $messageId]);
    }

    public function getLastMessageIdForUser(int $conversationId, int $userId): ?string
    {
        return $this->db->table(ConversationMessage::TABLE . ' as m')
            ->leftJoin(ConversationMessageUserState::TABLE . ' as s', function ($join) use ($userId) {
                $join->on('m.id', '=', 's.message_id')
                    ->where('s.user_id', '=', $userId);
            })
            ->where('m.conversation_id', $conversationId)
            ->whereNull('s.updated_at')
            ->orderBy('m.created_at', 'desc')
            ->value('m.id');
    }

    public function getConversationUsers(int $conversationId): array
    {
        return User::join(ConversationUser::TABLE, function ($join) use ($conversationId) {
            $join->on(ConversationUser::TABLE . '.user_id', '=', User::getTableName() . '.id')->where(ConversationUser::TABLE . '.conversation_id', $conversationId);
        })->get()->all();
    }

    public function getConversationAttachmentsSizeByUsers(int $conversationId): array
    {
        return $this->db->table(ConversationUser::TABLE)
            ->join(User::getTableName(), function ($join) {
                $join->on(User::getTableName() . '.id', '=', ConversationUser::TABLE . '.user_id');
            })
            ->leftJoin(ConversationMessageAttachment::TABLE, function ($join) use ($conversationId) {
                $join->on(ConversationUser::TABLE . '.conversation_id', '=', ConversationMessageAttachment::TABLE . '.conversation_id')
                    ->on(ConversationUser::TABLE . '.user_id', '=', ConversationMessageAttachment::TABLE . '.user_id')
                    ->where(ConversationMessageAttachment::TABLE . '.conversation_id', $conversationId);
            })
            ->where(ConversationUser::TABLE . '.conversation_id', $conversationId)
            ->groupBy('users.id')
            ->selectRaw('SUM(size) as size, users.id as user_id, users.name')
            ->get()->all();
    }

    public function addConversationMessageAttachment(array $data): int
    {
        return $this->db->table(ConversationMessageAttachment::TABLE)->insertGetId($data);
    }

    public function findExistsAttachment(int $conversationId, string $hash, ?int $ignoreId = null): ?stdClass
    {
        return $this->db->table(ConversationMessageAttachment::TABLE)
            ->where('conversation_id', $conversationId)
            ->where('hash', $hash)
            ->when($ignoreId, fn($q) => $q->whereNot('id', $ignoreId))
            ->first(['path', 'name']);
    }

    public function getMessageFiles(string $messageId): array
    {
        return $this->db->table(ConversationMessageAttachment::TABLE)
            ->where('conversation_message_id', $messageId)
            ->get(['id', 'path', 'size', 'name', 'hash', 'conversation_id', 'mime_type'])->all();
    }

    public function getMessageFile(string $messageId, int $fileId): ?stdClass
    {
        return $this->db->table(ConversationMessageAttachment::TABLE)
            ->where('id', $fileId)
            ->where('conversation_message_id', $messageId)
            ->first(['id', 'path', 'size', 'name', 'hash', 'conversation_id']);
    }

    public function deleteMessageFileModel(int $fileId): void
    {
        $this->db->table(ConversationMessageAttachment::TABLE)->where('id', $fileId)->delete();
    }

    public function getMessageById(string $messageId): ConversationMessage
    {
        return ConversationMessage::findOrFail($messageId);
    }

    public function renameMessageFile(int $fileId, string $name): void
    {
        $this->db->table(ConversationMessageAttachment::TABLE)->where('id', $fileId)->update(['name' => $name]);
    }

    public function saveLinks(int $conversationId, string $messageId, int $userId, array $links): void
    {
        $this->db->table(ConversationMessageLink::TABLE)->where('message_id', $messageId)->delete();

        if (empty($links)) {
            return;
        }

        $rows = [];

        foreach ($links as $link) {
            $rows[] = [
                'message_id'      => $messageId,
                'conversation_id' => $conversationId,
                'user_id'         => $userId,
                'url'             => $link,
                'host'            => parse_url($link, PHP_URL_HOST),
            ];
        }

        $this->db->table('conversation_message_links')->insert($rows);
    }

    public function getConversationUserInfo(int $conversationId, int $userId): stdClass
    {
        return $this->db->table(ConversationUser::TABLE)
            ->where(['conversation_id' => $conversationId, 'user_id' => $userId])
            ->first();
    }

    public function setConversationAsRestored(int $conversationId, int $userId): void
    {
        $this->db->table(ConversationUser::TABLE)
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->update(['deleted_at' => null]);
    }

    public function clearHistoryUserConversation(int $conversationId, int $userId): array
    {
        $messageIds = $this->db->table(ConversationMessage::TABLE)
            ->leftJoin(ConversationMessageUserState::TABLE, function ($join) use ($conversationId, $userId) {
                $join->on(ConversationMessage::TABLE . '.id', ConversationMessageUserState::TABLE . '.message_id')
                    ->where(ConversationMessageUserState::TABLE . '.user_id', '=', $userId);
            })
            ->where(ConversationMessage::TABLE . '.conversation_id', $conversationId)
            ->whereNull(ConversationMessageUserState::TABLE . '.message_id')
            ->pluck(ConversationMessage::TABLE . '.id')
            ->toArray();

        $rows = [];
        foreach ($messageIds as $messageId) {
            $rows[] = [
                'message_id' => $messageId,
                'user_id'    => $userId,
            ];
        }

        if (!empty($rows)) {
            $this->db->table(ConversationMessageUserState::TABLE)->insert($rows);
        }

        return $messageIds;
    }

    public function getRemovedMessageIds(int $count): array
    {
        return collect($this->db->select(
            <<<SQL
                SELECT m.id
                FROM conversation_messages m
                JOIN (
                    SELECT conversation_id, COUNT(*) AS participants_count
                    FROM conversation_users
                    WHERE deleted_at IS NULL
                    GROUP BY conversation_id
                ) cu ON m.conversation_id = cu.conversation_id
                WHERE m.deleted_by_count = cu.participants_count
                LIMIT {$count}
            SQL
        ))->pluck('id')->all();
    }
}