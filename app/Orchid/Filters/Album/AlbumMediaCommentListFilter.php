<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Album;

use App\Models\Albums\AlbumMediaComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;

class AlbumMediaCommentListFilter extends Filter
{
    public const array FIELDS = [
        'text',
    ];

    public static function runQuery(int $mediaId): Builder
    {
        return AlbumMediaComment::filters([self::class])
            ->join(User::getTableName(), User::getTableName() . '.id', '=', AlbumMediaComment::getTableName() . '.user_id')
            ->where('media_id', $mediaId)
            ->orderBy('created_at')
            ->selectraw(implode(',', [
                AlbumMediaComment::getTableName() . '.*',
                User::getTableName() . '.name as user_name',
                User::getTableName() . '.id as user_id',
                'users.avatar as user_avatar',
                AlbumMediaComment::getTableName() . '.body as body',
            ]));
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['text'])) {
            $builder->whereRaw('lower(body) like ?', "%{$input['text']}%");
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Group
    {
        $input = $request->all(self::FIELDS);

        return Group::make([
            Input::make('text')
                ->title('Search')
                ->value($input['text']),
        ]);
    }
}