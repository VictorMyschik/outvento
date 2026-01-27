<?php

namespace App\Orchid\Filters;

use App\Models\Reference\CategoryEquipment;
use App\Models\System\Translate;
use App\Models\System\TranslateGroup;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Language\Enum\TranslateGroupEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class TranslateFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'code',
        'text',
        'group',
    ];

    public static function runQuery(): Builder
    {
        return Translate::filters([self::class]);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (isset($input['text'])) {
            $value = htmlspecialchars((string)$input['text'], ENT_QUOTES);

            $builder->where(function () use ($builder, $value) {
                $builder->where('ru', 'LIKE', '%' . $value . '%')
                    ->orWhere('pl', 'LIKE', '%' . $value . '%')
                    ->orWhere('en', 'LIKE', '%' . $value . '%');
            });
        }

        if (isset($input['group'])) {
            $group = TranslateGroupEnum::from((int)$input['group']);

            $builder->join(TranslateGroup::getTableName(), function ($join) use ($group) {
                $join->on(Translate::getTableName() . '.id', '=', TranslateGroup::getTableName() . '.translate_id')
                    ->where(TranslateGroup::getTableName() . '.group', '=', $group->value);
            });
        }

        if (!empty($input['id'])) {
            $builder->where('id', (int)$input['id']);
        }

        if (isset($input['code']) && $value = htmlspecialchars((string)$input['code'], ENT_QUOTES)) {
            $builder->where('code', 'LIKE', '%' . $value . '%');
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        return Layout::rows([
            Group::make([
                Input::make('id')->value($input['id'])->title('ID'),
                Input::make('code')->value($input['code'])->title('Code'),
                Select::make('group')
                    ->options(TranslateGroupEnum::getSelectList())
                    ->title('Translate Group')
                    ->value($input['group'])
                    ->empty(),
                Input::make('text')->value($input['text'])->title('Text'),
            ]),

            ViewField::make('')->view('space'),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
