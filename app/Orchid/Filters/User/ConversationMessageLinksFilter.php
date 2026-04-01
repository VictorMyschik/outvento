<?php

declare(strict_types=1);

namespace App\Orchid\Filters\User;

use App\Models\Conversations\ConversationMessage;
use App\Models\Conversations\ConversationMessageLink;
use App\Models\Conversations\ConversationMessageUserState;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;

class ConversationMessageLinksFilter extends Filter
{
    public const array FIELDS = [
        'content',
        'userIds',
        'messageId',
        'url'
    ];

    public static function runQuery(int $conversationId): Builder
    {
        return ConversationMessageLink::filters([self::class])
            ->join(ConversationMessage::TABLE, function ($query) use ($conversationId) {
                $query->on(ConversationMessageLink::TABLE . '.message_id', '=', ConversationMessage::TABLE . '.id')
                    ->where(ConversationMessage::TABLE . '.conversation_id', $conversationId);
            })
            ->join('users', 'users.id', '=', ConversationMessage::TABLE . '.user_id')
            ->leftJoin(ConversationMessageUserState::TABLE, function ($query) use ($conversationId) {
                $query->on(ConversationMessageUserState::TABLE . '.message_id', '=', ConversationMessage::TABLE . '.id');
            })
            ->whereNull(ConversationMessageUserState::TABLE . '.updated_at')
            ->where(ConversationMessageLink::TABLE . '.conversation_id', $conversationId)
            ->orderBy(ConversationMessageLink::TABLE . '.created_at', 'ASC')
            ->selectRaw(implode(',', [
                ConversationMessage::TABLE . '.id as message_id',
                'users.name as user_name',
                'users.id as user_id',
                ConversationMessageLink::TABLE . '.id as id',
                ConversationMessageLink::TABLE . '.url',
                ConversationMessageLink::TABLE . '.created_at',
                ConversationMessageLink::TABLE . '.conversation_id as conversation_id',
            ]));
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['messageId'])) {
            $builder->where(ConversationMessageLink::TABLE . '.message_id', $input['messageId']);
        }

        if (!empty($input['content'])) {
            $builder->whereRaw('lower(url) like ?', "%{$input['content']}%");
        }

        if (!empty($input['userIds'])) {
            $builder->whereIn(ConversationMessageLink::TABLE . '.user_id', $input['userIds']);
        }

        if (!empty($input['url'])) {
            $builder->whereRaw('lower(url) like ?', "%{$input['url']}%");
        }

        return $builder;
    }
}
