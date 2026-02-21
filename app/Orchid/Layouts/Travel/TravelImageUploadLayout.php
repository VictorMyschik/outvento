<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Rows;

class TravelImageUploadLayout extends Rows
{
    public function fields(): array
    {
        return [
            Upload::make('travel.images')->groups('photo')->maxFiles(20)->path('tmp')
        ];
    }
}
