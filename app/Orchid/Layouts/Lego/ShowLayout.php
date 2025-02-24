<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Lego;

use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class ShowLayout extends Rows
{
    public function fields(): array
    {
        if (!$this->query->get('job')) {
            return [];
        }

        return [
            ViewField::make('')->view('admin.system.failed_jobs')->value(
                $this->query->get('job')
            ),
        ];
    }
}
