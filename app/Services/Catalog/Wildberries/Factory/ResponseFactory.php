<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\Factory;

use App\Services\Catalog\Wildberries\WBClientResponseInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class ResponseFactory
{
    public static function getResponse(ResponseInterface $httpResponse, string $targetResponseClass): WBClientResponseInterface
    {
        if ($httpResponse->getStatusCode() >= 400) {
            throw new RuntimeException(message: $httpResponse->getReasonPhrase(), code: $httpResponse->getStatusCode());
        }

        $decoded = json_decode((string)$httpResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return $targetResponseClass::fromArray($decoded);
    }
}
