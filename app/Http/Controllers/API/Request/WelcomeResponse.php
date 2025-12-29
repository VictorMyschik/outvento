<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Request;

final readonly class WelcomeResponse
{
    public function __construct(
        //  "lang": {
        //            "account": "Профиль",
        //            "register": "Регистрация",
        //            "login": "Вход",
        // }
        public array $lang,
        public array $travelTypeList, // of TravelTypeComponent
        public array $travelExamples,
    ) {}
}