<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Response\PaginationResponse;
use App\Http\Controllers\Controller;
use App\Services\System\Enum\Language;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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
        // Header: X-Locale
        $locale = request()->header('X-Locale')
            ?? throw new BadRequestHttpException('X-Locale header is required');

        try {
            return Language::fromCode($locale);
        } catch (\ValueError) {
            throw new UnprocessableEntityHttpException('Unsupported locale value');
        }
    }
}
