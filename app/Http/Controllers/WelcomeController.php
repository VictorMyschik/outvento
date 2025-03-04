<?php

namespace App\Http\Controllers;

use App\Services\Language\TranslateService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class WelcomeController extends Controller
{
    public function index(): View|Application|Factory
    {
        $out = [
            'lang' => TranslateService::getFullList($this->getLanguage())
        ];

        return View('welcome')->with($out);
    }
}
