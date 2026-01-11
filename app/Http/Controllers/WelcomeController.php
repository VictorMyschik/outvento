<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Language\TranslateService;
use App\Services\Travel\Api\TravelApiService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class WelcomeController extends Controller
{
    public function __construct(private readonly TravelApiService $service) {}

    public function index(): View|Application|Factory
    {
        $out = [
            'lang'           => TranslateService::getFullList($this->getLanguage()),
            'travelTypeList' => $this->service->getTravelTypeList($this->getLanguage()),
            'travelExamples' => $this->service->travelExamples($this->getLanguage()),
        ];

        return View('welcome')->with($out);
    }

    public function searchTravelPage(): View|Application|Factory
    {
        $out = [
            'lang' => TranslateService::getFullList($this->getLanguage()),
        ];

        return View('search_page')->with($out);
    }
}
