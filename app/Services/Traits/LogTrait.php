<?php

declare(strict_types=1);

namespace App\Services\Traits;

use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;

trait LogTrait
{
    private function logRequest(string $requestId, mixed $payload, string $apiMethod, string $path, array $headers = []): void
    {
        $this->log->info('Request ID: ' . $requestId, [
            'apiMethod' => $apiMethod,
            'path'      => $path,
            'payload'   => $payload,
            'headers'   => $headers,
        ]);
    }

    private function logError(\Throwable $exception, string $requestId, ?ResponseInterface $response, string $apiMethod, string $path): void
    {
        if ($response === null) {
            $response = $this->extractResponse($exception);
        }

        $this->log->error('Error ID: ' . $requestId, [
            'apiMethod' => $apiMethod,
            'path'      => $path,
            'response'  => json_decode((string)$response?->getBody(), true),
            'exception' => $exception->getMessage(),
        ]);
    }

    private function logResponse(string $requestId, mixed $payload, string $apiMethod, string $path, int $time): void
    {
        $this->log->info('Response ID: ' . $requestId, [
            'apiMethod' => $apiMethod,
            'path'      => $path,
            'payload'   => $payload,
            'time'      => $time . ' sec',
        ]);
    }

    private function extractResponse(\Throwable $exception): ?ResponseInterface
    {
        if ($exception instanceof ClientException) {
            return $exception->getResponse();
        }

        $previous = $exception->getPrevious();
        if ($previous !== null) {
            return $this->extractResponse($previous);
        }

        return null;
    }
}
