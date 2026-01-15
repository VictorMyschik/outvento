<?php

namespace App\Orchid\Filters;

use App\Models\User;
use App\Models\UserInfo\Communicate;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class UserCommunicateFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'user_id',
        'name',
        'email',
        'type',
        'address',
        'description'
    ];

    public static function runQuery()
    {
        $query = Communicate::filters([self::class])
            ->join(User::getTableName(), Communicate::getTableName() . '.user_id', '=', 'users.id');

        $query->select(
            'users.id as user_id',
            'users.name as name',
            'users.email as email',
            'communicate.*',
            'type',
            'address',
            'description',
            'communicate.created_at as created_at',
            'communicate.updated_at as updated_at',
        );

        return $query->paginate(20);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->only($this->parameters());

        if (!empty($input['user_id'])) {
            $builder->where('users.id', (int)$input['user_id']);
        }

        if (isset($input['email']) && !empty($input['email'])) {
            $input['email'] = substr($input['email'], 0, 255);
            $builder->where('email', $input['email']);
        }

        if (isset($input['name']) && !empty($input['name'])) {
            $builder->where('name', $input['name']);
        }

        // Address kind
        if (isset($input['kind']) && is_numeric($input['kind'])) {
            if (isset(Communicate::getKindList()[$input['kind']])) {
                $builder->where('communicate.kind', (int)$input['kind']);
            }
        }

        // Address
        if (isset($input['address']) && !empty($input['address'])) {
            $input['address'] = substr($input['address'], 0, 255);
            $builder->where('communicate.address', 'like', '%' . $input['address'] . '%');
        }

        return $builder->orderBy('id', 'desc');
    }

    public static function displayFilterCard(): Rows
    {
        $outLine[] = Input::make('id')
            ->type('number')
            ->value(request()->get('id'))
            ->title('User ID');

        $outLine[] = Input::make('email')
            ->value(request()->get('email'))
            ->title('Email (registered)');

        $outLine[] = Input::make('login')
            ->value(request()->get('login'))
            ->title('Login');


        $outLine2[] = Input::make('full_name')
            ->value(request()->get('full_name'))
            ->title('Full name');

        $outLine[] = Select::make('gender')
            ->empty()
            ->options([-1 => 'all', -2 => 'unknown'] + UserInfo::getGenderList())
            ->value(request()->get('gender'))
            ->title('Gender');

        $outLine2[] = Select::make('kind')
            ->empty()
            ->options([-1 => 'all'] + Communicate::getKindList())
            ->value(request()->get('kind'))
            ->title('Address kind');

        $outLine2[] = Input::make('address')
            ->value(request()->get('address'))
            ->title('Address');

        $data = [
            Group::make($outLine),
            Group::make($outLine2),
            ViewHelper::space(),
            ActionFilterPanel::getActionsButtons(),
        ];

        return Layout::rows($data);
    }
}
