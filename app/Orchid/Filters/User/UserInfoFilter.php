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
        'email_verified_at',
        'telegram_chat_id',
        'language',
        'first_name',
        'last_name',
        'gender',
        'birthday',
        'about',
        'created_at',
        'updated_at',
    ];

    public static function runQuery(): Builder
    {
        return User::filters([self::class]);
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

        if (isset($input['email_verified_at'])) {
            if ((bool)$input['email_verified_at']) {
                $builder->whereNotNull('email_verified_at');
            } else {
                $builder->whereNull('email_verified_at');
            }
        }

        if (!empty($input['telegram_chat_id'])) {
            $input['telegram_chat_id'] = substr($input['telegram_chat_id'], 0, 100);
            $builder->where('telegram_chat_id', 'like', '%' . $input['telegram_chat_id'] . '%');
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

        if (!empty($input['first_name'])) {
            $input['first_name'] = substr($input['first_name'], 0, 100);
            $builder->where('first_name', 'like', '%' . $input['first_name'] . '%');
        }

        if (!empty($input['last_name'])) {
            $input['last_name'] = substr($input['last_name'], 0, 100);
            $builder->where('last_name', 'like', '%' . $input['last_name'] . '%');
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

        if (!empty($input['created_at'])) {
            $builder->whereDate('created_at', $input['created_at']);
        }

        if (!empty($input['updated_at'])) {
            $builder->whereDate('updated_at', $input['updated_at']);
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
        $outLine[] = Select::make('email_verified_at')
            ->title('Email verified')
            ->options([
                '1' => 'Verified',
                '0' => 'Not verified',
            ])
            ->value($input['email_verified_at'])
            ->empty('Any');


        $outLine2[] = Input::make('telegram_chat_id')
            ->value($input['telegram_chat_id'])
            ->title('Telegram Chat ID');

        $outLine2[] = Input::make('first_name')
            ->value($input['first_name'])
            ->title('First name');

        $outLine2[] = Input::make('last_name')
            ->value($input['last_name'])
            ->title('Last name');

        $outLine2[] = Select::make('gender')
            ->title('Gender')
            ->options(Gender::getSelectList())
            ->value($input['gender'])
            ->empty('[не выбрано]');

        $outLine2[] = DateTimer::make('created_at')
            ->title('Created at')
            ->format('d.m.Y')
            ->value($input['created_at']);

        $outLine2[] = DateTimer::make('updated_at')
            ->title('Updated at')
            ->format('d.m.Y')
            ->value($input['updated_at']);

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
