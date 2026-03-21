<?php

declare(strict_types=1);

namespace App\Repositories\Conversations;

use App\Models\Conversations\Conversation;
use App\Models\Conversations\ConversationMessage;
use App\Models\Conversations\ConversationUser;
use App\Repositories\DatabaseRepository;
use App\Services\Conversations\ConversationRepositoryInterface;
use App\Services\Conversations\Enum\Type;
use Illuminate\Support\Str;

final readonly class ConversationRepository extends DatabaseRepository implements ConversationRepositoryInterface
{
    public function getConversationByUsers(int $ownerId, int $userId): ?int
    {
        $count = $ownerId === $userId ? 1 : 2;

        return $this->db->table(ConversationUser::TABLE)
            ->whereIn('user_id', [$ownerId, $userId])
            ->groupBy('conversation_id')
            ->havingRaw('count(conversation_id) > ' . $count)
            ->value('conversation_id');
    }

    public function addConversation(int $ownerId, int $userId, Type $type): int
    {
        $conversationId = $this->db->table(Conversation::getTableName())->insertGetId(['type' => $type->value]);

        $participantsData[] = [
            'conversation_id' => $conversationId,
            'user_id'         => $ownerId,
        ];

        if ($ownerId !== $userId) {
            $participantsData[] = [
                'conversation_id' => $conversationId,
                'user_id'         => $userId,
            ];
        }

        $this->db->table(ConversationUser::TABLE)->insertOrIgnore($participantsData);

        return $conversationId;
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
}