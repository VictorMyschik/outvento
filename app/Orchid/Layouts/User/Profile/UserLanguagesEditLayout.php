<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Profile;

use App\Models\LanguageName;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Rows;

final class UserLanguagesEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Relation::make('languages')
                ->title('Languages')
                ->multiple()
                //->value($this->query->get('values', []))
                ->fromModel(LanguageName::class, 'name', 'language_id'),
        ];
    }
}