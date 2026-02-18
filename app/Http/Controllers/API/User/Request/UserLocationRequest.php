<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User\Request;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserLocationRequest",
    required: ["placeId", "lat", "lng"],
    properties: [
        new OA\Property(property: "placeId", type: "string", maxLength: 255, example: "ChIJN1t_tDeuEmsRUsoyG83frY4"),
        new OA\Property(property: "lat", type: "number", format: "float", example: -33.8670522),
        new OA\Property(property: "lng", type: "number", format: "float", example: 151.1957362),
        new OA\Property(property: "countryCode", type: "string", maxLength: 2, example: "US"),
        new OA\Property(property: "cityName", type: "string", maxLength: 255, example: "Sydney"),
    ],
    type: "object"
)]
class UserLocationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'placeId'     => 'required|string|max:255',
            'lat'         => 'required|numeric|between:-90,90',
            'lng'         => 'required|numeric|between:-180,180',
            'countryCode' => 'required|string|size:2',
            'cityName'    => 'nullable|string|max:255',
        ];
    }

    public function getPlaceId(): string
    {
        return $this->input('placeId');
    }

    public function getLat(): float
    {
        return (float)$this->input('lat');
    }

    public function getLng(): float
    {
        return (float)$this->input('lng');
    }

    public function getCountryCode(): string
    {
        return $this->input('countryCode');
    }

    public function getCityName(): ?string
    {
        return $this->input('cityName');
    }
}
