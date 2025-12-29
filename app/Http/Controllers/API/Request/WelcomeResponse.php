<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WelcomeResponse',
    description: 'Response returned by the welcome endpoint',
    required: ['lang', 'travelTypeList', 'travelExamples'],
    properties: [
        new OA\Property(property: 'lang', description: 'Localization object (key => string)', type: 'object', example: "{'login': 'Вход'}"),
        new OA\Property(property: 'travelTypeList', description: 'Array of TravelTypeComponent objects', type: 'array', items: new OA\Items(ref: '#/components/schemas/TravelTypeComponent')),
        new OA\Property(property: 'travelExamples', description: 'Array of TravelListByTypeComponent objects', type: 'array', items: new OA\Items(ref: '#/components/schemas/TravelListByTypeComponent')),
    ]
)]
final readonly class WelcomeResponse
{
    public function __construct(
        public array $lang,
        public array $travelTypeList,
        public array $travelExamples,
    ) {}
}