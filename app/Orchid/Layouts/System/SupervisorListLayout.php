<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\System;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class SupervisorListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('pid', 'PID')->sort(),
            TD::make('statename', 'State Name')->sort(),
            TD::make('name', 'Name')->sort(),
            TD::make('group', 'Group')->sort(),
            TD::make('state', 'State')->sort(),
            TD::make('start', 'Start')->render(fn($model) => $model['start'] ? date('H:i:s d.m.Y', $model['start']) : 'N/A')->sort(),
            TD::make('stop', 'Stop')->render(fn($model) => $model['stop'] ? date('H:i:s d.m.Y', $model['stop']) : 'N/A')->sort(),
            TD::make('description', 'Description')->sort(),
            TD::make('exitstatus', 'Exit Status')->sort(),
            TD::make('stderr_logfile', 'Error Log')->sort(),
            TD::make('stdout_logfile', 'Output Log')->sort(),
        ];
    }

    public function hoverable(): bool
    {
        return true;
    }
}