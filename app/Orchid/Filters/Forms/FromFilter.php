<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Forms;

use App\Models\Forms\Form;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Forms\Enum\FormType;
use App\Services\System\Enum\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class FromFilter extends Filter
{
    public const array FIELDS = [
        'active',
        'language',
        'type',
        'contact',
        'userId',
        'description',
        'createdAt',
    ];

    public static function runQuery()
    {
        return Form::filters([self::class])->paginate(20);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!is_null($input['active'])) {
            $builder->where('active', (bool)$input['active']);
        }

        if (!empty($input['language'])) {
            $builder->where('language', Language::from($input['language'])->value);
        }

        if (!empty($input['contact'])) {
            $builder->where('contact', 'LIKE', '%' . $input['contact'] . '%');
        }

        if (!empty($input['description'])) {
            $builder->where('description', 'LIKE', '%' . $input['description'] . '%');
        }

        if (!empty($input['userId'])) {
            $builder->where('userId', (int)$input['userId']);
        }

        if (!is_null($input['type'])) {
            $builder->where('type', (int)$input['type']);
        }

        if (!empty($input['createdAt'])) {
            $builder->whereDate('created_at', '=', $input['createdAt']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        $group = [
            Group::make([
                Select::make('active')
                    ->options([null => 'Все', 1 => 'Прочитано', 0 => 'Не прочитано'])
                    ->value($input['active'])
                    ->title('Прочитано'),

                Select::make('language')
                    ->title('Язык')
                    ->value($input['language'])
                    ->empty('Все')
                    ->options(Language::getSelectList()),

                Select::make('type')
                    ->options([null => 'Все'] + FormType::getSelectList())
                    ->value($input['type'])
                    ->title('Тип заявки'),

                Input::make('contact')->value($input['contact'])->title('Contact'),
                Input::make('description')->value($input['description'])->title('Свой комментарий'),
            ]),

            Group::make([
                Input::make('userId')->value($input['userId'])->title('User ID'),
                Input::make('createdAt')->type('date')->value($input['createdAt'])->title('Дата создания'),
            ]),

            ViewField::make('')->view('space'),
            ActionFilterPanel::getActionsButtons()
        ];

        return Layout::rows($group);
    }
}