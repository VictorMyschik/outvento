<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Actions\Goods\ShowAction;
use Illuminate\Http\JsonResponse;

class GoodsController extends APIController
{
    public function show(string $entityType, int $entityId, ShowAction $action): JsonResponse
    {
        return $this->apiResponse($action->execute($entityType, $entityId));
    }
}
