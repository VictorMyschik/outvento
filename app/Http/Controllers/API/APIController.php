<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Response\PaginationResponse;
use App\Http\Controllers\Controller;
use App\Services\System\Enum\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[OA\Info(
    version: "1.0.0",
    description: "Для действий требуется авторизация с помощью метода Bearer Token",
    title: "Мои магазины",
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
        return Language::fromCode(app()->getLocale());
    }
}
