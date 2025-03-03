<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reference;

use App\Http\Controllers\Controller;
use App\Services\References\API\ReferenceApiService;
use Illuminate\Http\JsonResponse;

class ReferenceController extends Controller
{
    public function __construct(private ReferenceApiService $service) {}

    public function getFullReferences(): JsonResponse
    {
        return $this->successResult(
            $this->service->getFullReference($this->getLanguage())
        );
    }

    public function getCountryList(): JsonResponse
    {
        return $this->successResult(
            $this->service->getCountrySelectList($this->getLanguage())
        );
    }

    public function getUsingCountryList(): JsonResponse
    {
        return $this->successResult(
            $this->service->getUsingCountrySelectList($this->getLanguage())
        );
    }
}
