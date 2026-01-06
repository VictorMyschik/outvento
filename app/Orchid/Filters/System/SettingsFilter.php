<?php

namespace App\Orchid\Filters\System;

use App\Models\System\Settings;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class SettingsFilter extends Filter
{
    public const array FIELDS = [
        'active',
        'category',
        'codeKey',
        'name',
        'value',
    ];

    public static function queryQuery(): iterable
    {
        return Settings::filters([self::class])->paginate(20);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all();

        if (isset($input['name'])) {
            $value = htmlspecialchars((string)$input['name'], ENT_QUOTES);

            if ($value !== '') {
                $builder->where('name', 'LIKE', '%' . $value . '%');
            }
        }

        if (isset($input['codeKey'])) {
            $value = htmlspecialchars((string)$input['codeKey'], ENT_QUOTES);

            if ($value !== '') {
                $builder->where('code_key', 'LIKE', '%' . $value . '%');
            }
        }

        if (isset($input['value'])) {
            $value = htmlspecialchars((string)$input['value'], ENT_QUOTES);

            if ($value !== '') {
                $builder->where('value', 'LIKE', '%' . $value . '%');
            }
        }

        if (!is_null($input['active'] ?? null)) {
            $builder->where('active', (bool)$input['active']);
        }

        if ($input['category'] ?? null) {
            if (count($input['category']) !== 0) {
                $builder->whereIn('category', $input['category']);
            }
        }

        return $builder;
    }

    public static function displayFilterCard(): Rows
    {
        return Layout::rows([
            Group::make([
                Select::make('active')
                    ->options([1 => 'active', 0 => 'not active'])
                    ->empty('Все')
                    ->value(request()->get('active'))
                    ->title('Активно'),

                Select::make('category')
                    ->fromQuery(Settings::groupBy('category', 'id'), 'category', 'category')
                    ->multiple()
                    ->empty('Все')
                    ->value(request()->get('category'))
                    ->title('Категория'),

                Input::make('name')->value(request()->get('name'))->title('Наименование'),
                Input::make('code_key')->value(request()->get('codeKey'))->title('Key (in code)'),
                Input::make('value')->value(request()->get('value'))->title('Value'),
            ]),

            ViewField::make('')->view('space'),

            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
