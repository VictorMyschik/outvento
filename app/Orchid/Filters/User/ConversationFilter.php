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
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class ConversationFilter extends Filter
{
    public const array FIELDS = [
        'type',
        'userId',
    ];

    private static function selectRaw(): array
    {
        return [
            ConversationUser::TABLE . '.conversation_id as conversation_id',
            'title',
            'users.id as user_id',
            'users.name as name',
            'users.email as email',
            'CONCAT(users.first_name, \' \', users.last_name) as full_name',
            ConversationMessage::TABLE . '.content as content',
            ConversationMessage::TABLE . '.created_at as created_at',
        ];
    }

    public static function runQuery(int $userId): Builder
    {
        $conversationIds = ConversationUser::join(Conversation::TABLE, function ($join) {
            $join->on(Conversation::TABLE . '.id', '=', ConversationUser::TABLE . '.conversation_id')
                ->where(Conversation::TABLE . '.type', Type::Private->value);
        })
            ->where('user_id', $userId)
            ->whereNull(ConversationUser::TABLE . '.deleted_at')
            ->where('type', Type::Private->value)
            ->pluck('conversation_id')->toArray();

        $singleConversation = ConversationUser::whereIn(ConversationUser::TABLE . '.conversation_id', $conversationIds)
            ->whereNull(ConversationUser::TABLE . '.deleted_at')
            ->groupBy(ConversationUser::TABLE . '.conversation_id')
            ->havingRaw("count(" . ConversationUser::TABLE . ".conversation_id) = 1")
            ->value('conversation_id');

        return ConversationUser::filters([self::class])
            ->whereNot(ConversationUser::TABLE . '.user_id', $userId)
            ->whereIn(ConversationUser::TABLE . '.conversation_id', $conversationIds)
            ->when($singleConversation, function ($query) use ($singleConversation) {
                $query->unionAll(
                    ConversationUser::where(ConversationUser::TABLE . '.conversation_id', '=', $singleConversation)
                        ->join('users', 'users.id', '=', ConversationUser::TABLE . '.user_id')
                        ->leftJoin(ConversationMessage::TABLE, function ($join) {
                            $join->on(ConversationMessage::TABLE . '.conversation_id', '=', ConversationUser::TABLE . '.conversation_id')
                                ->where(ConversationMessage::TABLE . '.created_at', function ($query) {
                                    $query->selectRaw('MAX(created_at)')
                                        ->from(ConversationMessage::TABLE)
                                        ->whereColumn('conversation_id', ConversationUser::TABLE . '.conversation_id');
                                });
                        })
                        ->selectRaw(implode(',', self::selectRaw()))
                );
            });
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        $builder->join('users', 'users.id', '=', ConversationUser::TABLE . '.user_id');
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

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        $outLine[] = Select::make('type')
            ->title('Type')
            ->options(Type::getSelectList())
            ->value($input['type'])
            ->empty('Any');
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