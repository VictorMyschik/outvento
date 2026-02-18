<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\References;

use App\Models\Reference\City;
use App\Models\Reference\UserLocation;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class UserLocationLocation extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('user_id', 'User')->render(fn(UserLocation $userLocation) => $userLocation->getUser()->email)->sort(),
            TD::make('city_id', 'City')->render(fn(UserLocation $userLocation) => $userLocation->getCity()->name)->sort(),
            TD::make('lat', 'Latitude')->sort(),
            TD::make('lng', 'Longitude')->sort(),
            TD::make('radius_km', 'Radius (km)')->sort(),
            TD::make('is_visible', 'Visible')->render(fn(UserLocation $userLocation) => $userLocation->is_visible ? 'Yes' : 'No')->sort(),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(City $city) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the city?'))
                            ->method('remove', ['id' => $city->id]),
                    ])),
        ];
    }
}
