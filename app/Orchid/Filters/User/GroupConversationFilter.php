<?php

declare(strict_types=1);

namespace App\Orchid\Filters\User;

use App\Models\Conversations\Conversation;
use App\Models\Conversations\ConversationMessage;
use App\Models\Conversations\ConversationUser;
use App\Models\User;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Conversations\Enum\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class GroupConversationFilter extends Filter
{
    public const array FIELDS = [
        'title',
        'userId',
    ];

    private static function selectRaw(): array
    {
        return [
            ConversationUser::TABLE . '.conversation_id as conversation_id',
            Conversation::TABLE . '.title as title',
            ConversationMessage::TABLE . '.content as content',
            ConversationMessage::TABLE . '.created_at as created_at',
        ];
    }

    public static function runQuery(int $userId): Builder
    {
        $conversationIds = ConversationUser::join(Conversation::TABLE, function ($join) {
            $join->on(Conversation::TABLE . '.id', '=', ConversationUser::TABLE . '.conversation_id')
                ->where(Conversation::TABLE . '.type', Type::Group->value);
        })
            ->where('user_id', $userId)
            ->whereNull(ConversationUser::TABLE . '.deleted_at')
            ->pluck('conversation_id')->toArray();

        return ConversationUser::filters([self::class])
            ->whereNot(ConversationUser::TABLE . '.user_id', $userId)
            ->whereIn(ConversationUser::TABLE . '.conversation_id', $conversationIds)
            ->join('users', 'users.id', '=', ConversationUser::TABLE . '.user_id');
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        $builder->join(Conversation::TABLE, Conversation::TABLE . '.id', '=', ConversationUser::TABLE . '.conversation_id');
        $builder->leftJoin(ConversationMessage::TABLE, function ($join) {
            $join->on(ConversationMessage::TABLE . '.conversation_id', '=', ConversationUser::TABLE . '.conversation_id')
                ->where(ConversationMessage::TABLE . '.created_at', function ($query) {
                    $query->selectRaw('MAX(created_at)')
                        ->from(ConversationMessage::TABLE)
                        ->whereColumn('conversation_id', ConversationUser::TABLE . '.conversation_id');
                });
        });

        $builder->selectRaw(implode(',', self::selectRaw()));

        if (!empty($input['userId'])) {
            $builder->where('users.id', $input['userId']);
        }

        if (!empty($input['title'])) {
            $title = mb_strtolower($input['title']);
            $builder->whereRaw('lower(title) like ?', "%$title%");
        }

        $builder->groupBy(
            ConversationUser::TABLE . '.conversation_id',
            Conversation::TABLE . '.title',
            ConversationMessage::TABLE . '.content',
            ConversationMessage::TABLE . '.created_at',);

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        $outLine[] = Input::make('title')
            ->title('Title')
            ->value($input['title']);

        $outLine[] = Select::make('userId')
            ->title('User')
            ->fromModel(User::class, 'name')
            ->value($input['userId'])
            ->empty('Any');

        return Layout::rows([
            Group::make($outLine),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}