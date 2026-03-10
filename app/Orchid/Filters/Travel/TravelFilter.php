<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Travel;

use App\Models\Travel\Travel;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\System\Enum\Language;
use App\Services\Travel\Enum\TravelStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class TravelFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'language',
        'title',
        'preview',
        'description',
        'status',
        'start_city_id',
        'date_from',
        'date_to',
        'members',
        'members_exists',
        'public_id',
        'private_id',
        'visible',
        'archived_at',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public static function runQuery(): Builder
    {
        return Travel::filters([self::class]);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['id'])) {
            $builder->where('id', (int)$input['id']);
        }

        if (!is_null($input['title'])) {
            $builder->where('title', $input['title']);
        }

        if (!empty($input['preview'])) {
            $builder->whereRaw('lower(preview) like lower(?)', ['%' . mb_strtolower($input['preview']) . '%']);
        }

        if (!empty($input['description'])) {
            $builder->whereRaw('lower(description) like (?)', ['%' . mb_strtolower($input['description']) . '%']);
        }

        if (!empty($input['language'])) {
            $builder->where('language', (int)$input['language']);
        }

        if (!empty($input['deleted_at'])) {
            $builder->whereDate('deleted_at', $input['deleted_at']);
        }

        if (!empty($input['created_at'])) {
            $builder->whereDate('created_at', $input['created_at']);
        }

        if (!empty($input['updated_at'])) {
            $builder->whereDate('updated_at', $input['updated_at']);
        }

        if (!empty($input['archived_at'])) {
            $builder->whereDate('archived_at', $input['archived_at']);
        }


        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        return Layout::rows([
            Group::make([
                Input::make('id')->type('number')->value($input['id'])->title('ID'),
                Select::make('language')
                    ->title('Language')
                    ->options(Language::getSelectList())
                    ->value($input['language'])
                    ->empty(),
                Input::make('title')->value($input['title'])->title('Title'),
                Select::make('status')
                    ->options(TravelStatus::getSelectList())
                    ->value($input['status'])
                    ->empty('Все')
                    ->title('Status'),
            ]),

            Group::make([
                Input::make('archived_at')->type('date')->value($input['archived_at'])->title('Дата архивирования'),
                Input::make('deleted_at')->type('date')->value($input['deleted_at'])->title('Дата удаления'),
                Input::make('created_at')->type('date')->value($input['created_at'])->title('Дата создания'),
                Input::make('updated_at')->type('date')->value($input['updated_at'])->title('Дата обновления'),
            ]),

            ViewField::make('')->view('space'),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
