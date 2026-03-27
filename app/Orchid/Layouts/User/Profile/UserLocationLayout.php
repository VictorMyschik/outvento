<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Profile;

use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

final class UserLocationLayout extends Rows
{
    public function fields(): array
    {
        return [
            ViewField::make('')->view('admin.google-places'),
        ];
    }
}