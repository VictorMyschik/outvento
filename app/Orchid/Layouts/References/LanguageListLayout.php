<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\References;

use App\Models\Language;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class LanguageListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('name', 'Name')->sort(),
            TD::make('code', 'Code')->sort(),
            TD::make('', 'Names')->render(function (Language $language) {
                return ModalToggle::make('')
                    ->icon('eye')
                    ->modal('language_names_modal')
                    ->modalTitle('Names for language  ' . $language->name)
                    ->asyncParameters(['languageId' => $language->id]);
            }),
        ];
    }
}