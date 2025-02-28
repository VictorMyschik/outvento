<?php

namespace App\Http\Controllers;

use App\Jobs\MyJob;
use Illuminate\Support\Facades\DB;
use Stichoza\GoogleTranslate\GoogleTranslate;

/**
 * Тестовый клас для экспериментов и чернового
 */
class TestController extends Controller
{
    public function index()
    {
        $this->translateCountryNames();
    }

    function translateCountryNames(): void
    {
        $translator = new GoogleTranslate();
        $translator->setSource('ru'); // Исходный язык

        $countries = DB::table('countries')->get();

        foreach ($countries as $country) {
            if (empty($country->name_en)) {
                $translator->setTarget('en');
                $translatedName = $translator->translate($country->name);
                DB::table('countries')->where('id', $country->id)->update(['name_en' => $translatedName]);
            }

            if (empty($country->name_pl)) {
                $translator->setTarget('pl');
                $translatedName = $translator->translate($country->name);
                DB::table('countries')->where('id', $country->id)->update(['name_pl' => $translatedName]);
            }
        }
    }
}
