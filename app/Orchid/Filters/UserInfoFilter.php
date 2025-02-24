<?php

declare(strict_types=1);

namespace App\Orchid\Filters;

use App\Models\User;
use App\Models\UserInfo;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class UserInfoFilter extends Filter
{
    public const array FIELDS = ['id', 'user_id', 'login', 'full_name', 'email', 'gender'];

    public static function runQuery()
    {
        $query = UserInfo::filters([self::class])
            ->join(User::getTableName(), 'user_info.user_id', '=', 'users.id');

        $query->select(
            'user_id',
            'user_info.id as id',
            'name',
            'email',
            'users.created_at',
            'full_name',
            'gender',
            'birthday',
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

        if (isset($input['id']) && is_numeric($input['id']) && $input['id'] > 0) {
            $builder->where('users.id', $input['id']);
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

        return $builder->orderBy('id', 'desc');
    }

    public static function displayFilterCard(): Rows
    {
        $outLine[] = Input::make('id')
            ->type('number')
            ->value(request()->get('id'))
            ->title('ID');

        $outLine[] = Input::make('email')
            ->value(request()->get('email'))
            ->title('Email');

        $outLine[] = Input::make('login')
            ->value(request()->get('login'))
            ->title('Login');


        $outLine2[] = Input::make('full_name')
            ->value(request()->get('full_name'))
            ->title('Full name');

        $outLine2[] = Select::make('gender')
            ->empty()
            ->options([-1 => 'all', -2 => 'unknown'] + UserInfo::getGenderList())
            ->value(request()->get('gender'))
            ->title('Gender');


        $data = [
            Group::make($outLine),
            Group::make($outLine2),

            ActionFilterPanel::getActionsButtons(),
        ];

        return Layout::rows($data);
    }
}
