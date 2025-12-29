<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Response\PaginationResponse;
use App\Http\Controllers\Controller;
use App\Services\System\Enum\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "Для некоторых действий требуется авторизация с помощью Bearer Token",
    title: "API Documentation"
)]
abstract class APIController extends Controller
{
    public function apiResponse(array|object $data = [], int $code = 200): JsonResponse
    {
        if ($code < 200) {
            $code = 500;
        }

        $output = $this->buildResponse((array)$data);

        return response()->json($output, $code);
    }

    public function withPaginate(LengthAwarePaginator $paginator): JsonResponse
    {
        return response()->json(
            $this->buildResponse([
                'items'      => $paginator->items(),
                'pagination' => new PaginationResponse(
                    quantity: $paginator->count(),
                    totalQuantity: $paginator->total(),
                    currentPage: $paginator->currentPage(),
                    pages: $paginator->lastPage()
                ),
            ])
        );
    }

    public function withCustomPaginate(LengthAwarePaginator $paginator, array $customData): JsonResponse
    {
        return response()->json(
            $this->buildResponse($customData + [
                    'items'      => $paginator->items(),
                    'pagination' => new PaginationResponse(
                        quantity: $paginator->count(),
                        totalQuantity: $paginator->total(),
                        currentPage: $paginator->currentPage(),
                        pages: $paginator->lastPage()
                    ),
                ])
        );
    }

    private function buildResponse(array $content): array
    {
        return [
            'status'  => 'ok',
            'content' => $content,
        ];
    }

    protected function getLanguage(): Language
    {
        // Header: Accept-Language
        $locale = request()->getPreferredLanguage(array_keys(Language::getCodeWithLabel()));
        return Language::fromCode($locale);
    }
}
