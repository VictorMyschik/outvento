<?php

declare(strict_types=1);

namespace App\Orchid\Screens\References;

use App\Models\Reference\City;
use App\Orchid\Layouts\References\CityListLayout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class CitiesScreen extends Screen
{
    public string $name = 'Справочник городов';

    public function query(): iterable
    {
        return [
            'list' => City::filters([])->paginate(50)
        ];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            CityListLayout::class,
        ];
    }

    public function remove(int $id): void
    {
        try {
            City::loadByOrDie($id)->delete();
        } catch (\Exception $e) {
            Toast::error($e->getMessage());
        }
    }
}
