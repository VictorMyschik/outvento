<?php

namespace App\Orchid\Filters\System;

use App\Jobs\Enum\QueueJob;
use App\Models\Job;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class JobsFilter extends Filter
{
    public const array FIELDS = [
        'payload',
        'queue',
    ];

    public function name(): string
    {
        return 'Setup';
    }

    public static function runQuery()
    {
        return Job::filters([self::class])->paginate(20);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all();

        if (isset($input['payload'])) {
            $value = htmlspecialchars((string)$input['payload'], ENT_QUOTES);

            if ($value !== '') {
                $builder->where(fn() => $builder->where('payload', 'LIKE', '%' . $value . '%'));
            }
        }

        if (!empty($input['queue']) && QueueJob::tryFrom($input['queue'])) {
            $builder->where('queue', $input['queue']);
        }

        return $builder;
    }

    public static function displayFilterCard(): Rows
    {
        return Layout::rows([
            Group::make([
                Input::make('payload')->maxlength(50)->value(request()->get('payload'))->title('Payload'),

                Select::make('queue')
                    ->options([null => 'all'] + QueueJob::getSelectList())
                    ->value(is_null(request()->get('queue')) ? null : (int)request()->get('queue'))
                    ->title('Queue'),
            ]),

            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
