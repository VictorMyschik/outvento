<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Pagination",
    properties: [
        new OA\Property(property: "quantity", description: "Количество элементов на странице", type: "integer"),
        new OA\Property(property: "totalQuantity", description: "Общее количество элементов", type: "integer"),
        new OA\Property(property: "currentPage", description: "Текущая страница", type: "integer"),
        new OA\Property(property: "pages", description: "Общее количество страниц", type: "integer"),
    ],
    type: "object"
)]
final readonly class PaginationResponse
{
    public function __construct(
        public int $quantity,
        public int $totalQuantity,
        public int $currentPage,
        public int $pages,
    ) {}

    public static function empty(): self
    {
        return new self(0, 0, 0, 0);
    }
}
