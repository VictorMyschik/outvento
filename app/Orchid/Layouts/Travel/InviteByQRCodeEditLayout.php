<?php

namespace App\Orchid\Layouts\Travel;

use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class InviteByQRCodeEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Group::make([
                Input::make('qr_code')
                    ->type('text')
                    ->title('QR code')
                    ->placeholder('QR code')
                    ->required()
            ])
        ];

    }
}
