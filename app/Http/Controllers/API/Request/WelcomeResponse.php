<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Request;

use App\Services\Language\API\Response\TranslateResponse;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WelcomeResponse',
    description: 'Response returned by the welcome endpoint',
    required: ['lang', 'travelTypeList', 'travelExamples'],
    properties: [
        new OA\Property(property: 'lang', ref: '#/components/schemas/TranslateResponse', description: 'Localization object'),
        new OA\Property(property: 'travelTypeList', description: 'Array of TravelTypeComponent objects', type: 'array', items: new OA\Items(ref: '#/components/schemas/TravelTypeComponent')),
        new OA\Property(property: 'travelExamples', description: 'Array of TravelListByTypeComponent objects', type: 'array', items: new OA\Items(ref: '#/components/schemas/TravelListByTypeComponent')),
    ]
)]
final readonly class WelcomeResponse
{
    public function __construct(
        public TranslateResponse $lang,
        public array             $travelTypeList,
        public array             $travelExamples,
    ) {}
}