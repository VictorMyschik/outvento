<?php

declare(strict_types=1);

namespace App\Orchid\Filters\User;

use App\Models\Travel\Travel;
use App\Models\Travel\UIT;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\TravelVisible;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class UserTravelFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'title',
        'preview',
        'description',
        'status',
        'startCityId',
        'dateFrom',
        'dateTo',
        'members',
        'membersExists',
        'publicId',
        'visible',
        'archivedAt',
        'deletedAt',
        'createdAt',
        'updatedAt',
    ];

    public static function runQuery(int $userId): Builder
    {
        return Travel::filters([self::class])
            ->join(UIT::getTableName(), UIT::getTableName() . '.travel_id', '=', Travel::getTableName() . '.id')
            ->where('user_id', $userId)
            ->select(UIT::getTableName() . '.user_id', Travel::getTableName() . '.*')
            ->orderBy('id', 'desc');
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['id'])) {
            $builder->where(Travel::getTableName() . '.id', (int)$input['id']);
        }

        if (!empty($input['title'])) {
            $builder->whereRaw('LOWER(' . Travel::getTableName() . '.title) like ?', ['%' . mb_strtolower($input['title']) . '%']);
        }

        if (!empty($input['preview'])) {
            $builder->whereRaw('LOWER(' . Travel::getTableName() . '.preview) like ?', ['%' . mb_strtolower($input['preview']) . '%']);
        }

        if (!empty($input['description'])) {
            $builder->where(Travel::getTableName() . '.description', 'like', '%' . $input['description'] . '%');
        }

        if (isset($input['status'])) {
            $builder->where(Travel::getTableName() . '.status', (int)$input['status']);
        }


        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        $outLine[] = Input::make('id')
            ->type('number')
            ->value($input['id'])
            ->title('ID');

        $outLine[] = Select::make('status')
            ->options(TravelStatus::getSelectList())
            ->empty('Все')
            ->value($input['status'])
            ->title('Status');
        $outLine[] = Select::make('visible')
            ->options(TravelVisible::getSelectList())
            ->empty('Все')
            ->value($input['visible'])
            ->title('Visible');

        $outLine[] = Input::make('public_id')
            ->value($input['publicId'])
            ->title('Public ID');

        $outLine2[] = Input::make('travel.date_from')
            ->title('Date from')
            ->value($input['dateFrom'])
            ->type('date');

        $outLine2[] = Input::make('travel.date_to')
            ->title('Date to')
            ->value($input['dateTo'])
            ->type('date');

        $outLine3[] = Input::make('title')
            ->value($input['title'])
            ->title('Title');

        $outLine3[] = Input::make('preview')
            ->value($input['preview'])
            ->title('Preview');


        $outLine3[] = Input::make('description')
            ->value($input['description'])
            ->title('Description');

        return Layout::rows([
            Group::make($outLine),
            Group::make($outLine2),
            Group::make($outLine3),
            ViewField::make('')->view('space'),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}