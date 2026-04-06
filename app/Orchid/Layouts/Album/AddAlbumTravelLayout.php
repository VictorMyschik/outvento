<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Album;

use App\Models\Travel\Travel;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Rows;

class AddAlbumTravelLayout extends Rows
{
    public function fields(): array
    {
        return [
            Relation::make('travel_id')
                ->title('Travel')
                ->fromModel(Travel::class, 'title', 'id')
                ->required(),
        ];
    }
}