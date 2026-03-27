<?php

declare(strict_types=1);

namespace App\Services\User\Google;

use Psr\Http\Message\ResponseInterface;

final readonly class ResponseFactory
{
    public static function getResponse(ResponseInterface $httpResponse, ?string $targetResponseClass): mixed
    {
        $decoded = json_decode((string)$httpResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if ($targetResponseClass === null) {
            return $decoded;
        }

        return $targetResponseClass::fromArray($decoded);
    }
}
