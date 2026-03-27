<?php

declare(strict_types=1);

namespace App\Orchid\Filters\References;

use App\Models\Language;
use App\Models\LanguageName;
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

class UserLanguageFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'code',
        'name',
    ];

    public static function runQuery(): Builder
    {
        return Language::filters([self::class])->select(
            Language::getTableName() . '.id',
            Language::getTableName() . '.code',
            LanguageName::getTableName() . '.name as name',
        );
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);
        $builder->join(LanguageName::getTableName(), LanguageName::getTableName() . '.language_id', '=', Language::getTableName() . '.id')
            ->where(LanguageName::getTableName() . '.locale', app()->getLocale());

        if (!empty($input['id'])) {
            $builder->where(Language::getTableName() . '.id', $input['id']);
        }

        if (!empty($input['code'])) {
            $builder->where('code', $input['code']);
        }

        if (!empty($input['name'])) {
            $builder->where('name', $input['name']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        return Layout::rows([
            Group::make([
                Input::make('id')
                    ->value($input['id'])
                    ->title('ID'),
                Select::make('name')
                    ->fromQuery(LanguageName::where('locale', app()->getLocale()), 'name', 'name')
                    ->empty('Все')
                    ->value($input['name'])
                    ->title('Name'),
                Input::make('code')
                    ->value($input['code'])
                    ->title('Code'),
            ]),
            ViewField::make('')->view('space'),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}