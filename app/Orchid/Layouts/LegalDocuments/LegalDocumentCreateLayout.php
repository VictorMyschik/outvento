<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\LegalDocuments;

use App\Services\Other\LegalDocuments\Enum\LegalDocumentType;
use App\Services\System\Enum\Language;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class LegalDocumentCreateLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('language')
                ->options(Language::getSelectList())
                ->title('Язык'),
            Select::make('type')
                ->options(LegalDocumentType::getSelectList())
                ->title('Тип документа'),
        ];
    }
}