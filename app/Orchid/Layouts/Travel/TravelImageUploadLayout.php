<?php

namespace App\Orchid\Layouts\Travel;

use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Rows;

class TravelImageUploadLayout extends Rows
{
    public function fields(): array
    {
        return [
            Upload::make('travel.image')->groups('photo')->maxFiles(20)->path('tmp')
        ];
    }
}
