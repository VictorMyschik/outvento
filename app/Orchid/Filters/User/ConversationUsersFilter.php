<?php

declare(strict_types=1);

namespace App\Orchid\Filters\User;

use App\Models\Conversations\ConversationUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;

class ConversationUsersFilter extends Filter
{
    public const array FIELDS = [
        'userIds',
        'email',
    ];

    public static function runQuery(int $conversationId): Builder
    {
        return User::filters([self::class])
            ->join(ConversationUser::TABLE, 'user_id', '=', User::getTableName() . '.id')
            ->where(ConversationUser::TABLE . '.conversation_id', $conversationId)
            ->select(User::getTableName() . '.*');
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['userIds'])) {
            $builder->whereIn(User::getTableName() . '.id', $input['userIds']);
        }

        if (!empty($input['email'])) {
            $builder->where('users.email', $input['email']);
        }

        return $builder;
    }
}
