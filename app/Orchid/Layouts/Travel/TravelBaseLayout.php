<?php

namespace App\Orchid\Layouts\Travel;

use App\Models\Travel;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Sight;
use Orchid\Support\Facades\Layout;

class TravelBaseLayout extends Rows
{
    public function fields(): array
    {
        /** @var Travel $travel */
        $travel = $this->query['travel'];

        return [
            Layout::legend('', [
                Sight::make('id', 'ID'),
                Sight::make('name'),
                Sight::make('description'),
            ]),
            /*  Layout::legend('user', [
                Sight::make('id'),
              ]),*/
            // Label::make('country')->title('Страна')->value($travel->getCountry()->getName()),
            //Label::make('type')->title('Тип')->value($travel->getTravelType()->getName())->name('type'),
        ];
    }
}
