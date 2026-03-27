<?php

declare(strict_types=1);

namespace App\Orchid\Screens\References;

use App\Models\Reference\City;
use App\Models\Reference\UserLocation;
use App\Orchid\Layouts\References\UserLocationLocation;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class UserLocationScreen extends Screen
{
    public string $name = 'Справочник локаций пользователей';

    public function query(): iterable
    {
        return [
            'list' => UserLocation::filters([])->paginate(50)
        ];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            UserLocationLocation::class,
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
