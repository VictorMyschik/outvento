<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Promo;

use App\Services\Notifications\Enum\PromoEvent;
use App\Services\System\Enum\Language;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class SubscriptionEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('subscription.language')->options(Language::getSelectList())->required()->title('Language'),
            Select::make('subscription.event')
                ->options(PromoEvent::getSelectList())
                ->title('Тип'),
            Input::make('subscription.email')->type('email')->max(100)->required()->title('Email')
        ];
    }
}
