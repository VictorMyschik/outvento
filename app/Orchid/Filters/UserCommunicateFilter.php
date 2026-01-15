<?php

namespace App\Orchid\Filters;

use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Models\UserInfo\CommunicationType;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\User\Enum\Gender;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class UserCommunicateFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'userId',
        'name',
        'email',
        'type',
        'full_name',
        'address',
        'telegram_chat_id',
        'description',
        'createdAt',
        'updatedAt',
    ];

    public static function runQuery()
    {
        $query = Communication::filters([self::class])
            ->rightJoin(User::getTableName(), Communication::getTableName() . '.user_id', '=', 'users.id');

        $query->selectRaw(implode(', ', [
                'users.id as user_id',
                'users.name as name',
                'users.email as email',
                'users.telegram_chat_id as telegram_chat_id',
               // 'CONCAT(users.first_name, " ", users.last_name) as full_name',
                'communicates.*',
                'type',
                'address',
                'description',
                'communicates.created_at as created_at',
                'communicates.updated_at as updated_at',
            ])
        );

        return $query;
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->only($this->parameters());

        if (!empty($input['id'])) {
            $builder->where('id', (int)$input['id']);
        }

        if (!empty($input['userId'])) {
            $builder->where('users.id', (int)$input['userId']);
        }

        if (isset($input['email']) && !empty($input['email'])) {
            $input['email'] = substr($input['email'], 0, 255);
            $builder->where('email', $input['email']);
        }

        if (!empty($input['name'])) {
            $builder->where('name', $input['name']);
        }

        if (!empty($input['telegram_chat_id'])) {
            $builder->where('telegram_chat_id', $input['telegram_chat_id']);
        }

        if (!empty($input['full_name'])) {
            $builder->whereRaw('CONCAT(users.first_name, " ", users.last_name) like ?', ['%' . $input['full_name'] . '%']);
        }

        if (!empty($input['type'])) {
            $builder->where('communicates.type', (int)$input['type']);
        }

        if (isset($input['address']) && !empty($input['address'])) {
            $input['address'] = substr($input['address'], 0, 255);
            $builder->where('communicates.address', 'like', '%' . $input['address'] . '%');
        }

        if (!empty($input['description'])) {
            $builder->where('description', 'like', '%' . $input['description'] . '%');
        }

        if (!empty($input['createdAt'])) {
            $builder->whereDate('communicates.created_at', $input['createdAt']);
        }

        if (!empty($input['updatedAt'])) {
            $builder->whereDate('communicates.updated_at', $input['updatedAt']);
        }

        return $builder->orderBy('id', 'desc');
    }

    public static function displayFilterCard(): Rows
    {
        $outLine[] = Input::make('id')
            ->type('number')
            ->value(request()->get('id'))
            ->title('User ID');

        $outLine[] = Relation::make('email')
            ->fromModel(User::class, 'email')
            ->value(request()->get('email'))
            ->title('Email (registered)');

        $outLine[] = Relation::make('telegram_chat_id')
            ->fromModel(User::class, 'telegram_chat_id')
            ->value(request()->get('telegram_chat_id'))
            ->title('Telegram Chat ID');

        $outLine[] = Relation::make('login')
            ->fromModel(User::class, 'name')
            ->value(request()->get('login'))
            ->title('Login');

        $outLine2[] = Input::make('full_name')
            ->value(request()->get('full_name'))
            ->title('Full name');

        $outLine[] = Select::make('gender')
            ->empty('[не выбрано]')
            ->fromEnum(Gender::class)
            ->value(request()->get('gender'))
            ->title('Gender');

        $outLine2[] = Relation::make('type')
            ->fromModel(CommunicationType::class, 'name_ru')
            ->value(request()->get('type'))
            ->title('Type');

        $outLine2[] = Input::make('address')
            ->value(request()->get('address'))
            ->title('Address');

        $data = [
            Group::make($outLine),
            Group::make($outLine2),
            ViewField::make('')->view('space'),
            ActionFilterPanel::getActionsButtons(),
        ];

        return Layout::rows($data);
    }
}
