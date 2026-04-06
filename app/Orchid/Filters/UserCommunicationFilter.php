<?php

declare(strict_types=1);

namespace App\Orchid\Filters;

use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\User\Enum\CommunicationType;
use App\Services\User\Enum\Visibility;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class UserCommunicationFilter extends Filter
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
        'visibility',
        'createdAt',
        'updatedAt',
    ];

    public static function runQuery(): Builder
    {
        $query = Communication::filters([self::class])
            ->join(User::TABLE, Communication::getTableName() . '.user_id', '=', 'users.id');

        $query->selectRaw(implode(', ', [
                'communications.*',
                'users.id as user_id',
                'users.name as name',
                'users.email as email',
                'CONCAT(users.first_name, \' \', users.last_name) as full_name',
                'type as type',
                'address',
                'description',
                'communications.created_at as created_at',
                'communications.updated_at as updated_at',
            ])
        );

        return $query;
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->only($this->parameters());

        if (!empty($input['id'])) {
            $builder->where('users.id', (int)$input['id']);
        }

        if (!empty($input['userId'])) {
            $builder->where('users.id', (int)$input['userId']);
        }

        if (!empty($input['email'])) {
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
            $builder->where('type', (int)$input['type']);
        }

        if (isset($input['visibility'])) {
            $builder->where(Communication::getTableName() . '.visibility', (int)$input['visibility']);
        }

        if (isset($input['address']) && !empty($input['address'])) {
            $input['address'] = substr($input['address'], 0, 255);
            $builder->where('address', 'like', '%' . $input['address'] . '%');
        }

        if (!empty($input['description'])) {
            $builder->where('description', 'like', '%' . $input['description'] . '%');
        }

        return $builder;
    }

    public static function displayFilterCard(): Rows
    {
        $outLine[] = Input::make('id')
            ->type('number')
            ->value(request()->get('id'))
            ->title('User ID');

        $outLine[] = Relation::make('email')
            ->fromModel(User::class, 'email', 'email')
            ->value(request()->get('email'))
            ->title('Email (registered)');

        $outLine[] = Relation::make('telegram_chat_id')
            ->fromModel(User::class, 'telegram_chat_id')
            ->value(request()->get('telegram_chat_id'))
            ->title('Telegram Chat ID');

        $outLine[] = Relation::make('name')
            ->fromModel(User::class, 'name', 'name')
            ->value(request()->get('name'))
            ->title('Login');

        $outLine2[] = Input::make('full_name')
            ->value(request()->get('full_name'))
            ->title('Full name');

        $outLine2[] = Select::make('type')
            ->options(CommunicationType::getSelectList())
            ->value(request()->get('type'))
            ->empty('Все')
            ->title('Type');

        $outLine2[] = Select::make('visibility')
            ->options(Visibility::getSelectList())
            ->empty('Все')
            ->value(request()->get('visibility'))
            ->title('Visibility');

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
