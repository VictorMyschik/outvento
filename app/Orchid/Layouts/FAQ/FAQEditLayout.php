<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\FAQ;

use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Layouts\Rows;

class FAQEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Group::make([
                Switcher::make('faq.active')->sendTrueOrFalse()->title('Active'),

                Select::make('faq.language_id')
                    ->title('Language')
                    ->fromModel(\App\Models\System\Language::class, 'name', 'id'),
            ]),

            Input::make('faq.title')
                ->type('text')
                ->max(255)
                ->required()
                ->title('Title'),

            Quill::make('faq.text')
                ->title('Text')
        ];
    }
}
