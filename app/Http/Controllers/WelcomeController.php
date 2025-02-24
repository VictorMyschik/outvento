<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class WelcomeController extends Controller
{
    public function index(): View|Application|Factory
    {
        $out = array();

        return View('welcome')->with($out);
    }
}
