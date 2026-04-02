<?php

declare(strict_types=1);

namespace App\Orchid\Filters\User;

use App\Models\Conversations\ConversationMessage;
use App\Models\Conversations\ConversationMessageAttachment;
use App\Models\Conversations\ConversationMessageUserState;
use App\Models\User;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Conversations\ConversationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class ConversationMessageFilter extends Filter
{
    public const array FIELDS = [
        'content',
        'messageId',
        'userIds',
        'createdAt',
        'fileName',
        'url',
    ];

    public static function runQuery(int $conversationId, int $userId): Builder
    {
        return ConversationMessage::filters([self::class])
            ->join('users', 'users.id', '=', 'conversation_messages.user_id')
            ->leftJoin(ConversationMessageUserState::TABLE, function ($query) use ($conversationId, $userId) {
                $query->on(ConversationMessageUserState::TABLE . '.message_id', '=', ConversationMessage::TABLE . '.id')
                    ->where(ConversationMessageUserState::TABLE . '.user_id', $userId);
            })
            ->leftJoin(ConversationMessage::TABLE . ' as parent', 'parent.id', '=', 'conversation_messages.parent_id')
            ->whereNull(ConversationMessageUserState::TABLE . '.updated_at')
            ->where(ConversationMessage::TABLE . '.conversation_id', $conversationId)
            ->orderBy(ConversationMessage::TABLE . '.created_at', 'ASC')
            ->selectRaw(implode(',', [
                ConversationMessage::TABLE . '.*',
                'users.name as user_name',
                'users.id as user_id',
                $userId . ' as current_user_id',
                'parent.id as parent_id',
                'parent.content as parent_content',
                'parent.user_id as parent_user_id',
            ]));
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['content'])) {
            $builder->whereRaw('lower(content) like ?', "%{$input['content']}%");
        }

        if (!empty($input['userIds'])) {
            $builder->whereIn(ConversationMessage::TABLE . '.user_id', $input['userIds']);
        }

        if (!empty($input['messageId'])) {
            $builder->where(ConversationMessage::TABLE . '.id', $input['messageId']);
        }

        if (!empty($input['createdAt'])) {
            $builder->whereDate(ConversationMessage::TABLE . '.created_at', $input['createdAt']);
        }

        if (!empty($input['url'])) {
            $builder->whereRaw('lower(content) like ?', "%{$input['url']}%");
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request, ConversationService $conversations, int $conversationId): Rows
    {
        $userOptions = [];

        foreach ($conversations->getConversationUsers($conversationId) as $user) {
            $userOptions[$user->id] = $user->name;
        }

        $input = $request->all(self::FIELDS);

        return Layout::rows([
            Group::make([
                Input::make('messageId')
                    ->title('Message ID')
                    ->value($input['messageId']),
                Input::make('content')
                    ->title('Message')
                    ->value($input['content']),
                Select::make('userIds')
                    ->title('User')
                    ->multiple()
                    ->value($input['userIds'])
                    ->options($userOptions)
                    ->empty('Any'),
                DateTimer::make('createdAt')
                    ->title('Created At')
                    ->enableTime(false)
                    ->format24hr()
                    ->value($input['createdAt'] ?? null),
                Input::make('fileName')
                    ->title('File Name')
                    ->value($input['fileName']),
                Input::make('url')
                    ->title('URL')
                    ->value($input['url']),
            ]),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}