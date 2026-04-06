<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Album;

use App\Models\Albums\Album;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Albums\Enum\Visibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class AlbumListFilter extends Filter
{
    public const array FIELDS = [
        'title',
        'visibility',
    ];

    public static function runQuery(int $userId): Builder
    {
        return Album::filters([self::class])->where('user_id', $userId);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['title'])) {
            $builder->whereRaw('lower(title) like ?', "%{$input['title']}%");
        }

        if (!empty($input['visibility'])) {
            $builder->where('visibility', $input['visibility']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        return Layout::rows([
            Group::make([
                Input::make('title')
                    ->title('Title')
                    ->value($input['title']),
                Select::make('visibility')
                    ->title('Visibility')
                    ->empty('All')
                    ->options(Visibility::getSelectList())
                    ->value($input['visibility']),
            ]),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}