<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Rows;

class TravelMediaUploadLayout extends Rows
{
    public function fields(): array
    {
        if ($this->query->get('media')) {
            $out[] = Group::make([
                Switcher::make('media.is_avatar')->title('Is Avatar'),
                Input::make('media.sort')->title('Sorting')->type('number'),
            ]);

            $out[] = Input::make('media.description')->title('Description')->type('text');
        } else {
            $out = [
                Upload::make('travel.images')->groups('photo')->maxFiles(
                    $this->query->get('media') ? 1 : 10
                )->path('tmp')
            ];
        }

        return $out;
    }
}
