<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class InternalNotificationShowLayout extends Rows
{
    public function fields(): array
    {
        return [
            ViewField::make('')->view('admin.raw')->value('<pre>' . $this->query->get('notification')?->message . '</pre>'),
            ViewField::make('')->view('hr'),
            Group::make([
                Label::make('notification.read_at')->title('Read'),
                Label::make('notification.created_at')->title('Created'),
            ]),
        ];
    }
}
