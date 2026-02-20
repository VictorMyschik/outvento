<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Travel;

use App\Models\Travel\Travel;
use App\Services\System\Enum\Language;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class UserTravelListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', 'ID')->render(function (Travel $travel) {
                return Link::make((string)$travel->id)
                    ->stretched()
                    ->route('profiles.travel.details', ['user' => $travel->user_id, 'travel' => $travel->id]);
            })->sort(),
            TD::make('title', 'Title')->sort(),
            TD::make('status', 'Status')->render(fn(Travel $travel) => $travel->getStatus()->getLabel())->sort(),
            TD::make('date_from', 'Date from')->sort(),
            TD::make('date_to', 'Date to')->sort(),
            TD::make('public_id', 'Public ID')->sort(),
        ];
    }

    public function hoverable(): bool
    {
        return true;
    }
}
