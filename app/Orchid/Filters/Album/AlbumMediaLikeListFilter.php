<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Album;

use App\Models\Albums\AlbumMediaLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Select;

class AlbumMediaLikeListFilter extends Filter
{
    public const array FIELDS = [
        'name',
    ];

    public static function runQuery(int $mediaId): Builder
    {
        return AlbumMediaLike::filters([self::class])
            ->join(User::getTableName(), User::getTableName() . '.id', '=', AlbumMediaLike::getTableName() . '.user_id')
            ->where('media_id', $mediaId)
            ->orderBy('updated_at', 'desc')
            ->selectraw(implode(',', [
                AlbumMediaLike::getTableName() . '.media_id as media_id',
                AlbumMediaLike::getTableName() . '.icon as icon',
                AlbumMediaLike::getTableName() . '.updated_at as updated_at',
                User::getTableName() . '.name as user_name',
                User::getTableName() . '.id as user_id',
                'users.avatar as user_avatar',
            ]));
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['name'])) {
            $builder->whereRaw('lower(users.name) like ?', "%{$input['name']}%");
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Group
    {
        $input = $request->all(self::FIELDS);

        return Group::make([
            Select::make('name')
                ->title('User name')
                ->value($input['name']),
        ]);
    }
}