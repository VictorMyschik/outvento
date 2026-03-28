<?php

declare(strict_types=1);

namespace App\Orchid\Filters\User;

use App\Models\Conversations\ConversationMessage;
use App\Models\Conversations\ConversationMessageAttachment;
use App\Models\Conversations\ConversationMessageUserState;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;

class ConversationMessageAttachmentsFilter extends Filter
{
    public const array FIELDS = [
        'content',
        'userIds',
        'fileName',
        'messageId',
    ];

    public static function runQuery(int $conversationId): Builder
    {
        return ConversationMessageAttachment::filters([self::class])
            ->join(ConversationMessage::TABLE, function ($query) use ($conversationId) {
                $query->on(ConversationMessageAttachment::TABLE . '.conversation_message_id', '=', ConversationMessage::TABLE . '.id')
                    ->where(ConversationMessage::TABLE . '.conversation_id', $conversationId);
            })
            ->join('users', 'users.id', '=', ConversationMessage::TABLE . '.user_id')
            ->leftJoin(ConversationMessageUserState::TABLE, function ($query) use ($conversationId) {
                $query->on(ConversationMessageUserState::TABLE . '.message_id', '=', ConversationMessage::TABLE . '.id');
            })
            ->whereNull(ConversationMessageUserState::TABLE . '.updated_at')
            ->where(ConversationMessage::TABLE . '.conversation_id', $conversationId)
            ->orderBy(ConversationMessage::TABLE . '.created_at', 'ASC')
            ->selectRaw(implode(',', [
                ConversationMessage::TABLE . '.id as message_id',
                'users.name as user_name',
                'users.id as user_id',
                ConversationMessageAttachment::TABLE . '.id as id',
                ConversationMessageAttachment::TABLE . '.name',
                ConversationMessageAttachment::TABLE . '.size',
                ConversationMessageAttachment::TABLE . '.created_at',
                ConversationMessageAttachment::TABLE . '.conversation_id as conversation_id',
            ]));
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['messageId'])) {
            $builder->where(ConversationMessage::TABLE . '.id', $input['messageId']);
        }

        if (!empty($input['content'])) {
            $builder->whereRaw('lower(content) like ?', "%{$input['content']}%");
        }

        if (!empty($input['userIds'])) {
            $builder->whereIn(ConversationMessage::TABLE . '.user_id', $input['userIds']);
        }

        if (!empty($input['fileName'])) {
            $fileName = strtolower($input['fileName']);
            $builder->whereRaw('lower(' . ConversationMessageAttachment::TABLE . '.name' . ') like ?', "%{$fileName}%");
        }

        return $builder;
    }
}