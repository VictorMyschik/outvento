<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\LegalDocuments;

use App\Models\System\Settings;
use App\Services\Other\Enum\LegalDocumentType;
use App\Services\System\Enum\Language;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
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