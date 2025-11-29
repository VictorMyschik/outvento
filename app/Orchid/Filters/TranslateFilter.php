<?php

namespace App\Orchid\Filters;

use App\Models\Reference\CategoryEquipment;
use App\Models\System\Translate;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
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
        'code',
        'text',
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
                Input::make('code')->value($input['code'])->title('Code'),
                Input::make('text')->value($input['text'])->title('Text'),
            ]),

            ViewField::make('')->view('space'),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }

    private static function getCategoryList(): array
    {
        $category = array_unique(array_column(CategoryEquipment::all()->toArray(), 'name'));

        return array_combine($category, $category);
    }
}
