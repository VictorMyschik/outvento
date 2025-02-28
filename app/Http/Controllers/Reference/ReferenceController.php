<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reference;

use App\Http\Controllers\Controller;
use App\Services\References\ReferenceService;
use Illuminate\Http\JsonResponse;

class ReferenceController extends Controller
{
    public function __construct(private ReferenceService $service) {}

    public function getCountryList(): JsonResponse
    {
        return $this->successResult(
            $this->service->getCountrySelectList($this->getLanguage())
        );
    }
}
