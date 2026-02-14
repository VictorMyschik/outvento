<?php

declare(strict_types=1);

namespace App\Orchid\Filters\User;

use App\Models\User;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\System\Enum\Language;
use App\Services\User\Enum\Gender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class UserInfoFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'name',
        'email',
        'emailVerifiedAt',
        'subscriptionToken',
        'language',
        'firstName',
        'lastName',
        'gender',
        'birthday',
        'about',
        'createdAt',
        'updatedAt',
    ];

    public static function runQuery(): Builder
    {
        return User::filters([self::class])->orderBy('id', 'desc');
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['id'])) {
            $builder->where('id', (int)$input['id']);
        }

        if (!empty($input['email'])) {
            $input['email'] = substr($input['email'], 0, 100);
            $builder->where('email', 'like', '%' . $input['email'] . '%');
        }

        if (isset($input['emailVerifiedAt'])) {
            if ((bool)$input['emailVerifiedAt']) {
                $builder->whereNotNull('emailVerifiedAt');
            } else {
                $builder->whereNull('emailVerifiedAt');
            }
        }

        if (!empty($input['subscriptionToken'])) {
            $builder->where('subscription_token', 'like', '%' . $input['subscriptionToken'] . '%');
        }

        if (!empty($input['name'])) {
            $input['name'] = substr($input['name'], 0, 100);
            if (preg_match('/^[a-zA-Z0-9_]+$/', $input['name'])) {
                $builder->where('name', 'like', '%' . $input['name'] . '%');
            }
        }

        if (!empty($input['language'])) {
            $builder->where('language', (int)$input['language']);
        }

        if (!empty($input['firstName'])) {
            $input['firstName'] = substr($input['firstName'], 0, 100);
            $builder->where('first_name', 'like', '%' . $input['firstName'] . '%');
        }

        if (!empty($input['lastName'])) {
            $input['lastName'] = substr($input['lastName'], 0, 100);
            $builder->where('last_name', 'like', '%' . $input['lastName'] . '%');
        }

        if (!empty($input['gender']) && is_numeric($input['gender'])) {
            $builder->where('gender', (int)$input['gender']);
        }

        if (!empty($input['birthday'])) {
            $builder->whereDate('birthday', $input['birthday']);
        }

        if (!empty($input['about'])) {
            $builder->where('about', 'like', '%' . $input['about'] . '%');
        }
        if (!empty($input['subscriptionToken'])) {
            $builder->where('subscription_token', 'like', '%' . $input['subscriptionToken'] . '%');
        }

        if (!empty($input['createdAt'])) {
            $builder->whereDate('created_at', $input['createdAt']);
        }

        if (!empty($input['updatedAt'])) {
            $builder->whereDate('updated_at', $input['updatedAt']);
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

        $outLine[] = Input::make('email')
            ->value($input['email'])
            ->title('Email');

        $outLine[] = Input::make('name')
            ->value($input['name'])
            ->title('Login');

        $outLine[] = Select::make('language')
            ->title('Language')
            ->options(Language::getSelectList())
            ->value($input['language'])
            ->empty();
        $outLine[] = Select::make('emailVerifiedAt')
            ->title('Email verified')
            ->options([
                '1' => 'Verified',
                '0' => 'Not verified',
            ])
            ->value($input['emailVerifiedAt'])
            ->empty('Any');


        $outLine2[] = Input::make('firstName')
            ->value($input['firstName'])
            ->title('First name');

        $outLine2[] = Input::make('lastName')
            ->value($input['lastName'])
            ->title('Last name');

        $outLine2[] = Select::make('gender')
            ->title('Gender')
            ->options(Gender::getSelectList())
            ->value($input['gender'])
            ->empty('[не выбрано]');

        $outLine2[] = DateTimer::make('createdAt')
            ->title('Created at')
            ->format('d.m.Y')
            ->value($input['createdAt']);

        $outLine2[] = DateTimer::make('updatedAt')
            ->title('Updated at')
            ->format('d.m.Y')
            ->value($input['updatedAt']);

        $outLine2[] = DateTimer::make('birthday')
            ->title('Birthday')
            ->format('d.m.Y')
            ->value($input['birthday']);

        return Layout::rows([
            Group::make($outLine),
            Group::make($outLine2),

            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
