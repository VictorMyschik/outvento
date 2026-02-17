<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Fields\Label;
use Orchid\Screen\Layouts\Rows;

final class TelegramDeeplinkLayout extends Rows
{
    public function fields(): array
    {
        return [
            Label::make('link')->title('Add boot by deeplink')->value($this->query->get('link')),
        ];
    }
}