<?php

namespace App\Orchid\Layouts\System;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class MigrationLayout extends Table
{
    protected $target = 'migration-list';

    protected function columns(): iterable
    {
        $columns[] = TD::make('id', 'ID')->sort();
        $columns[] = TD::make('migration', 'Миграция')->sort();
        $columns[] = TD::make('batch', 'Пакет')->sort();

        $columns[] = TD::make('#', 'Действия')
            ->align(TD::ALIGN_CENTER)
            ->width('100px')
            ->render(function ($object) {
                return DropDown::make()->icon('options-vertical')->list([
                    Button::make('refresh')
                        ->icon('refresh')
                        ->confirm('Refresh the migration file from the database record? This will overwrite the existing migration file.')
                        ->method('runRefreshMigrationFile', ['migration' => $object->migration]),

                    Button::make('delete')
                        ->icon('trash')
                        ->confirm('Are you sure you want to delete this migration record? This action cannot be undone.')
                        ->method('remove', ['id' => $object->id]),

                ]);
            });

        return $columns;
    }

    public $title = 'Migrations';

    public function hoverable(): bool
    {
        return true;
    }
}
