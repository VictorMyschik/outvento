<?php

namespace App\Http\Controllers;

use App\Services\System\Enum\Language;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function successResult($data = null, int $code = 200): JsonResponse
    {
        return response()->json(['result' => true, 'content' => $data], $code);
    }

    public function errorResult($data, int $code = 400): JsonResponse
    {
        return response()->json(['result' => false, 'content' => $data], $code);
    }

    protected function getLanguage(): Language
    {
        return Language::fromCode(app()->getLocale());
    }
}
