<?php

declare(strict_types=1);

namespace App\Orchid\Filters\User;

use App\Models\Conversations\Conversation;
use App\Models\Conversations\ConversationMessage;
use App\Models\Conversations\ConversationUser;
use App\Models\User;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
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
        'userIds',
    ];

    private static function selectRaw(): array
    {
        return [
            ConversationUser::TABLE . '.conversation_id as conversation_id',
            'title',
        ];
    }

    public static function runQuery(int $userId): Builder
    {
        return Conversation::filters([])
            ->join(ConversationUser::TABLE, function ($query) use ($userId) {
                $query->on(Conversation::TABLE . '.id', '=', ConversationUser::TABLE . '.conversation_id')
                    ->where(ConversationUser::TABLE . '.user_id', $userId)
                    ->whereNull(ConversationUser::TABLE . '.deleted_at');
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

        $outLine[] = Input::make('title')
            ->title('Title')
            ->value($input['title']);
        $outLine[] = Select::make('userIds')
            ->title('User')
            ->fromModel(User::class, 'name')
            ->value($input['userIds'])
            ->empty('Any');

        return Layout::rows([
            Group::make($outLine),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}