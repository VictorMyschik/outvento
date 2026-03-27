<?php

declare(strict_types=1);

namespace App\Repositories\Conversations;

use App\Models\Conversations\Conversation;
use App\Models\Conversations\ConversationMessage;
use App\Models\Conversations\ConversationMessageUserState;
use App\Models\Conversations\ConversationUser;
use App\Models\User;
use App\Repositories\DatabaseRepository;
use App\Services\Conversations\ConversationRepositoryInterface;
use App\Services\Conversations\Enum\Role;
use App\Services\Conversations\Enum\Type;
use Illuminate\Support\Str;

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

    public function purgeConversation(int $conversationId): void
    {
        $this->db->table(ConversationUser::TABLE)
            ->where('conversation_id', $conversationId)
            ->update(['deleted_at' => now()]);
    }

    public function addMessage(int $conversationId, int $userId, string $text): void
    {
        $id = Str::ulid()->toBase32();

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
    }

    public function setConversationAsDeleted(?int $conversationId, int $userId): void
    {
        $this->db->table(ConversationUser::TABLE)
            ->when(!empty($conversationId), function ($query) use ($conversationId) {
                $query->where('conversation_id', $conversationId);
            })
            ->where('user_id', $userId)
            ->update(['deleted_at' => now()]);
    }

    public function deleteMessagesByConversationId(int $conversationId): void
    {
        do {
            $deleted = $this->db->table(ConversationMessage::TABLE)
                ->where('conversation_id', $conversationId)
                ->limit(1000)
                ->delete();

        } while ($deleted > 0);

        $this->db->table(ConversationUser::TABLE)->where('conversation_id', $conversationId)->delete();
        $this->db->table(Conversation::TABLE)->where('id', $conversationId)->delete();
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
        $this->db->table(ConversationMessage::TABLE)->where('id', $messageId)->increment('deleted_by_count');
        $this->db->commit();
    }

    public function updateMessage(string $messageId, string $content): void
    {
        $this->db->table(ConversationMessage::TABLE)->where('id', $messageId)->update(['content' => $content]);
    }

    public function deleteMessage(string $messageId): void
    {
        $this->db->table(ConversationMessage::TABLE)->where('id', $messageId)->delete();
        $this->db->table(ConversationMessageUserState::TABLE)->where('message_id', $messageId)->delete();
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
}