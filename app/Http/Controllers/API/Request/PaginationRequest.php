<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

#[OA\Parameter(
    name: "page",
    description: "Номер страницы",
    in: "query",
    schema: new OA\Schema(type: "integer", example: 1)
)]
#[OA\Parameter(
    name: "per_page",
    description: "Количество элементов на странице. Максимальное значение 1000",
    in: "query",
    schema: new OA\Schema(type: "integer", example: 10)
)]
#[OA\Parameter(
    name: "sort",
    description: "Поле для сортировки. (поставьте знак - (минус) перед полем, чтобы отсортировать по убыванию)",
    in: "query",
    schema: new OA\Schema(type: "string", example: "id")
)]
class PaginationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page'     => 'int',
            'per_page' => 'int|between:1,1000',
            'sort'     => 'string',
        ];
    }

    public function getPage(int $default): int
    {
        return (int)$this->get('page', $default);
    }

    public function getPerPage(int $default): int
    {
        return (int)$this->get('per_page', $default);
    }

    public function getSort(?string $default = null): ?string
    {
        if (!preg_match('/^[a-zA-Z0-9\-_]*$/', (string)$this->get('sort', $default))) {
            throw ValidationException::withMessages(['sort' => 'Поле для сортировки содержит недопустимые символы']);
        }

        $sort = $this->get('sort', $default);

        return $sort ? strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', ltrim((string)$sort, '-'))) : null;
    }

    public function getDirection(): string
    {
        $sort = (string)$this->get('sort');
        if (str_starts_with($sort, '-')) {
            return 'DESC';
        }

        return 'ASC';
    }

    public function messages(): array
    {
        return [
            'page.int'         => 'Номер страницы должен быть целым числом',
            'per_page.int'     => 'Количество элементов на странице должно быть целым числом',
            'per_page.between' => 'Количество элементов на странице должно быть от 1 до 1000',
            'sort.string'      => 'Поле для сортировки должно быть строкой',
        ];
    }
}
