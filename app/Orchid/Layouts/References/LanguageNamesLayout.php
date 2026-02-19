<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\References;

use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class LanguageNamesLayout extends Rows
{
    public function fields(): array
    {
        $rows = [];

        $rows['header'] = ['№', 'Language', 'Code', 'Name'];

        foreach ($this->query->get('names') as $key => $name) {
            $row = [];
            $row['№'] = $key + 1;
            $row['Language'] = $name->language_name;
            $row['Code'] = $name->locale;
            $row['Name'] = $name->name;

            $rows['body'][] = $row;
        }

        return [
            ViewField::make('')->view('admin.table')->value($rows)
        ];
    }
}