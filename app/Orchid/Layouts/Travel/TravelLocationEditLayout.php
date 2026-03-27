<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class TravelLocationEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            ViewField::make('')
                ->view('admin.map-view')
                ->value([
                    'lat' => $this->query->get('lat'),
                    'lng' => $this->query->get('lng'),
                ]),
        ];
    }
}