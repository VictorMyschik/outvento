<?php

declare(strict_types=1);

namespace App\Orchid\Filters\User;

use App\Models\Conversations\ConversationMessage;
use App\Models\Conversations\ConversationMessageUserState;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class ConversationMessageFilter extends Filter
{
    public const array FIELDS = [
        'content',
    ];

    public static function runQuery(int $conversationId, int $userId): Builder
    {
        return ConversationMessage::filters([self::class])
            ->join('users', 'users.id', '=', 'conversation_messages.user_id')
            ->leftJoin(ConversationMessageUserState::TABLE, function ($query) use ($conversationId, $userId) {
                $query->on(ConversationMessageUserState::TABLE . '.message_id', '=', ConversationMessage::TABLE . '.id')
                    ->where(ConversationMessageUserState::TABLE . '.user_id', $userId);
            })
            ->whereNull(ConversationMessageUserState::TABLE . '.updated_at')
            ->where('conversation_id', $conversationId)
            ->orderByDesc(ConversationMessage::TABLE . '.created_at')
            ->selectRaw(implode(',', [
                ConversationMessage::TABLE . '.*',
                'users.name as user_name',
                'users.id as user_id',
            ]));
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['content'])) {
            $builder->whereRaw('lower(content) like ?', "%{$input['content']}%");
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        $outLine[] = Input::make('content')
            ->title('Type')
            ->value($input['content']);

        return Layout::rows([
            Group::make($outLine),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}