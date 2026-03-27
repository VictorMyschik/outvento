<?php

declare(strict_types=1);

namespace App\Services\User\Google;

use App\Services\Traits\LogTrait;
use App\Services\User\Google\Response\TimeZoneResponse;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class GoogleClient
{
    use LogTrait;

    public function __construct(
        private ClientInterface $client,
        private LoggerInterface $log,
    ) {}

    public function getTimezone(string $url, array $request): TimeZoneResponse
    {
        $url = $url . '?' . http_build_query($request);

        $response = $this->send('GET', $url, $request, null, TimeZoneResponse::class, __FUNCTION__);

        return new TimeZoneResponse(...$response);
    }

    private function send(string $httpMethod, string $url, mixed $request, ?string $token, string $targetClass, string $method): array
    {
        $requestId = Uuid::v4()->toRfc4122();
        $headers = $this->buildHeaders($token);
        $payload = $request ? json_encode($request) : null;
        $this->logRequest($requestId, $payload, $method, $url, $headers);
        $time = microtime(true);

        try {
            $httpResponse = $this->client->send(
                request: new Request($httpMethod, $url),
                options: ['body' => null, 'headers' => $headers, 'timeout' => 100, 'connect_timeout' => 100],
            );
            $result = json_decode((string)$httpResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (ConnectException $e) {
            $this->logError($e, $requestId, null, $method, $url);

            throw $e;
        } catch (\Throwable $e) {
            $this->logError($e, $requestId, $httpResponse ?? null, $method, $url);
            throw $e;
        }

        $time = (int)(microtime(true) - $time);

        $this->logResponse($requestId, 'See Weekler API Log', $method, $url, $time);

        return $result;
    }

    private function sendPublicRequest(string $httpMethod, string $url, mixed $request, string $method): array
    {
        $requestId = Uuid::v4()->toRfc4122();
        $payload = $request ? json_encode($request) : null;
        $this->logRequest($requestId, $payload, $method, $url);
        $time = microtime(true);

        try {
            $httpResponse = $this->client->send(
                request: new Request($httpMethod, $url),
                options: ['body' => $payload, 'headers' => []],
            );
            $result = json_decode((string)$httpResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            $this->logError($e, $requestId, $httpResponse ?? null, $method, $url);

            throw $e;
        }

        $time = (int)(microtime(true) - $time);
        $this->logResponse($requestId, (string)$httpResponse->getBody(), $method, $url, $time);

        return $result;
    }

    private function buildHeaders(?string $token): array
    {
        $headers = ['Content-Type' => 'application/json'];

        if ($token) {
            $headers['Authorization'] = $token;
        }

        return $headers;
    }
}