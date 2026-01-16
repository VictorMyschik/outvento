<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use Stichoza\GoogleTranslate\GoogleTranslate;

/**
 * Тестовый клас для экспериментов и чернового
 */
class TestController extends Controller
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function index(Request $request)
    {
        $body = $request->all();

        $this->logger->info(json_encode($body, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        return Response()->json(status: 204);
    }

    function translateCountryNames(): void
    {
        $translator = new GoogleTranslate();
        $translator->setSource('ru'); // Исходный язык

        $tableName = 'travel_types';
        $travelTypes = DB::table($tableName)->get();

        foreach ($travelTypes as $travelType) {
            if (empty($travelType->name_en)) {
                $translator->setTarget('en');
                $translatedName = $translator->translate($travelType->name_ru);
                DB::table($tableName)->where('id', $travelType->id)->update(['name_en' => $translatedName]);
            }

            if (empty($travelType->name_pl)) {
                $translator->setTarget('pl');
                $translatedName = $translator->translate($travelType->name_ru);
                DB::table($tableName)->where('id', $travelType->id)->update(['name_pl' => $translatedName]);
            }
        }
    }
}
