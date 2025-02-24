<?php

namespace App\Orchid\Filters;

use App\Models\User;
use App\Models\UserInfo\Communicate;
use App\Models\UserInfo\UserInfo;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Orchid\Layouts\Lego\ViewHelper;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class UserInfoCommunicateFilter extends Filter
{
    private static array $fields = [
        'id',
        'user_id',
        'name',
        'email',
        'full_name',
        'gender',
        'kind',
        'address',
        'description'
    ];

    public function parameters(): ?array
    {
        return self::$fields;
    }

    public static function getFilterFields(): array
    {
        return self::$fields;
    }

    public static function runQuery()
    {
        $query = User::filters([self::class])
            ->join(Communicate::getTableName(), 'communicate.user_id', '=', 'users.id')
            ->leftJoin(UserInfo::getTableName(), 'user_info.user_id', '=', 'users.id');

        $query->select(
            'users.id as user_id',
            'communicate.id as id',
            'name',
            'email',
            'full_name',
            'gender',
            'kind',
            'address',
            'description',
            'communicate.created_at as created_at',
            'communicate.updated_at as updated_at',
        );

        // Final
        if ($sort = request()->get('sort')) {
            if (str_contains($sort, '-')) {
                $sort = str_replace('-', '', $sort);
                $query->orderByRaw('"' . $sort . '" ASC');
            } else {
                $query->orderByRaw('"' . $sort . '" DESC');
            }
        }

        return $query->paginate(20);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->only($this->parameters());

        if (isset($input['user_id']) && is_numeric($input['user_id']) && $input['user_id'] > 0) {
            $builder->where('users.id', $input['user_id']);
        }

        // email
        if (isset($input['email']) && !empty($input['email'])) {
            $input['email'] = substr($input['email'], 0, 255);
            // regex email
            if (preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', $input['email'])) {
                $builder->where('email', 'like', '%' . $input['email'] . '%');
            } else {
                $builder->whereNull('email');
            }
        }

        if (isset($input['login']) && !empty($input['login'])) {
            $input['login'] = substr($input['login'], 0, 255);
            // regex login
            if (preg_match('/^[a-zA-Z0-9_]+$/', $input['login'])) {
                $builder->where('name', 'like', '%' . $input['login'] . '%');
            }
        }

        // Full Name
        if (isset($input['full_name']) && !empty($input['full_name'])) {
            $input['full_name'] = substr($input['full_name'], 0, 255);
            // regex Full name
            $regex = '/^[a-zA-Zа-яА-ЯёЁ0-9\s\.\,\-]+$/u';
            if (preg_match($regex, $input['full_name'])) {
                $builder->where('full_name', 'like', '%' . $input['full_name'] . '%');
            }
        }

        // Gender
        if (isset($input['gender']) && is_numeric($input['gender'])) {
            if (isset(UserInfo::getGenderList()[$input['gender']])) {
                $builder->where('gender', (int)$input['gender']);
            }

            if ((int)$input['gender'] === -2) {
                $builder->whereNull('gender');
            }
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
