<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Travel;

use App\Models\Travel\Travel;
use App\Orchid\Filters\Travel\TravelFilter;
use App\Orchid\Layouts\Travel\TravelListLayout;
use App\Services\Travel\TravelService;
use Orchid\Screen\Screen;

class TravelListScreen extends Screen
{
    public string $name = 'Travel List';


    public function __construct(private readonly TravelService $service) {}

    public function query(): iterable
    {
        return [
            'list' => TravelFilter::runQuery()->paginate(20)
        ];
    }

    public function description(): ?string
    {
        return "Список путешествий";
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            TravelListLayout::class,
        ];
    }
}
