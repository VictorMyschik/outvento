<?php

declare(strict_types=1);

namespace App\Orchid\Filters\MessageLog;

use App\Models\MessageLog\EmailLog;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Email\Enum\EmailTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class MessageLogEmailFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'type',
        'email',
        'subject',
        'status',
        'created_at',
    ];

    public static function runQuery(): Builder
    {
        return EmailLog::filters([self::class]);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['id'])) {
            $builder->where('id', (int)$input['id']);
        }

        if (!empty($input['type'])) {
            $builder->where('type', (int)$input['type']);
        }

        if (!empty($input['email'])) {
            $builder->where('email', 'like', '%' . $input['email'] . '%');
        }

        if (!empty($input['subject'])) {
            $builder->whereRaw("lower(subject) like ?", ['%' . mb_strtolower($input['subject']) . '%']);
        }

        if (isset($input['status'])) {
            if ($input['status'] == 1) {
                $builder->where('status', 1);
            } elseif ($input['status'] == -1) {
                $builder->where('status', 0);
            }
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        $group = Group::make([
            Select::make('type')
                ->options(EmailTypeEnum::getSelectList())
                ->value($input['type'])
                ->empty()
                ->title('Тип письма'),

            Select::make('status')
                ->options([1 => 'Успешные', -1 => 'Проваленные'])
                ->value($input['status'])
                ->empty('Все')
                ->title('Статус письма'),

            Input::make('id')->value($input['id'])->type('number')->title('ID'),
            Input::make('subject')->value($input['subject'])->type('text')->title('Тема'),
            Input::make('email')->value($input['email'])->type('text')->title('Email адрес'),
        ]);

        return Layout::rows([$group, ViewField::make('')->view('space'), ActionFilterPanel::getActionsButtons($request->all())]);
    }
}