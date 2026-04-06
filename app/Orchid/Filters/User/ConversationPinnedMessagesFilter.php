<?php

declare(strict_types=1);

namespace App\Orchid\Filters\User;

use App\Models\Conversations\ConversationMessage;
use App\Models\Conversations\ConversationPinnedMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;

class ConversationPinnedMessagesFilter extends Filter
{
    public const array FIELDS = [
        'userIds',
        'email',
        'content',
        'messageId',
    ];

    public static function runQuery(int $conversationId): Builder
    {
        return ConversationPinnedMessage::filters([self::class])
            ->join(ConversationMessage::TABLE, ConversationMessage::TABLE . '.id', '=', ConversationPinnedMessage::TABLE . '.message_id')
            ->join(User::TABLE, ConversationMessage::TABLE . '.user_id', '=', User::TABLE . '.id')
            ->where(ConversationMessage::TABLE . '.conversation_id', $conversationId)
            ->selectRaw(implode(',', [
                ConversationMessage::TABLE . '.*',
                'users.name as user_name',
                'users.id as user_id',
                ConversationPinnedMessage::TABLE . '.created_at as pinned_at',
            ]));
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['userIds'])) {
            $builder->whereIn(User::TABLE . '.id', $input['userIds']);
        }

        if (!empty($input['email'])) {
            $builder->where(User::TABLE . '.email', $input['email']);
        }

        if (!empty($input['content'])) {
            $builder->whereRaw('lower(content) like ?', "%{$input['content']}%");
        }

        if (!empty($input['messageId'])) {
            $builder->where(ConversationMessage::TABLE . '.id', $input['messageId']);
        }

        return $builder;
    }
}
