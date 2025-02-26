<?php

namespace App\Orchid\Layouts\Subscription;

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
            Select::make('subscription.type')
                ->value($this->query->get('type_options_exists', []))
                ->options($this->query->get('type_options', []))
                ->multiple()
                ->title('Тип'),
            Input::make('subscription.email')->type('email')->max(100)->required()->title('Email')
        ];
    }
}
