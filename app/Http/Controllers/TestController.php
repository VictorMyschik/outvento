<?php

namespace App\Http\Controllers;

use App\Jobs\EmailJob;
use App\Jobs\MyJob;

/**
 * Тестовый клас для экспериментов и чернового
 */
class TestController extends Controller
{
    public function index()
    {
        MyJob::dispatch()->onConnection('database');
    }
}
