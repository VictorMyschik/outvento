<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\API;

use App\Services\Catalog\Wildberries\API\Response\AttributesResponse;
use App\Services\Catalog\Wildberries\API\Response\ChildGroupsResponse;
use App\Services\Catalog\Wildberries\API\Response\ParentGroupsResponse;
use App\Services\Catalog\Wildberries\Factory\ResponseFactory;
use App\Services\Catalog\Wildberries\WBClientResponseInterface;
use App\Services\Traits\LogTrait;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class WBClient
{
    use LogTrait;

    private const string WB_CATALOG_URL = 'https://basket-14.wbbasket.ru/vol2138/part213855/%s/info/ru/card.json';
    private const string WB_CATALOG_PAGE_URL = 'https://www.wildberries.ru/catalog/%s/detail.aspx';

    public function __construct(private ClientInterface $client, private LoggerInterface $log, private array $config) {}

    public function getGood(string $url): array
    {
        return $this->sendPublicRequest('GET', $url, null, __FUNCTION__);
    }

    #region Catalog
    public function getBaseGroups(string $token): WBClientResponseInterface
    {
        $url = $this->buildUrl('content', 'get_base_groups');
        return $this->sendPrivateRequest('GET', $url, null, $token, ParentGroupsResponse::class, __FUNCTION__);
    }

    public function getChildGroups(int $parentGroupId, string $token): WBClientResponseInterface
    {
        $url = $this->buildUrl('content', 'get_child_groups') . sprintf('?parentID=%s', $parentGroupId) . '&' . http_build_query(['limit' => 1000, 'offset' => 0]);

        return $this->sendPrivateRequest('GET', $url, null, $token, ChildGroupsResponse::class, __FUNCTION__);
    }

    public function getChildGroupsByOffset(int $limit, int $offset, string $token): WBClientResponseInterface
    {
        $url = $this->buildUrl('content', 'get_child_groups') . '?' . http_build_query(['limit' => $limit, 'offset' => $offset]);

        return $this->sendPrivateRequest('GET', $url, null, $token, ChildGroupsResponse::class, __FUNCTION__);
    }

    public function getGroupAttributes(int $groupId, string $token): WBClientResponseInterface
    {
        $url = sprintf($this->buildUrl('content', 'get_attributes'), $groupId);

        return $this->sendPrivateRequest('GET', $url, null, $token, AttributesResponse::class, __FUNCTION__);
    }

    #endregion

    public function doGetRaw(string $url, array $options = []): StreamInterface
    {
        try {
            $response = $this->client->get($url, $options);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }

        return $response->getBody();
    }

    public function getGoodByGroup(int $groupId): array
    {
        $url = 'https://catalog.wb.ru/catalog/jeans/v2/catalog?appType=1&dest=-1257786&page=2&xsubject=' . $groupId;

        return $this->sendPublicRequest('GET', $url, null, __FUNCTION__);
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

    private function sendPrivateRequest(string $httpMethod, string $url, mixed $request, string $token, string $targetClass, string $method): WBClientResponseInterface
    {
        $requestId = Uuid::v4()->toRfc4122();
        $headers = $this->buildHeaders($token);
        $payload = $request ? json_encode($request) : null;
        $this->logRequest($requestId, $payload, $method, $url, $headers);
        $time = microtime(true);

        try {
            $httpResponse = $this->client->send(
                request: new Request($httpMethod, $url),
                options: ['body' => $payload, 'headers' => $headers],
            );
            $result = ResponseFactory::getResponse($httpResponse, $targetClass);
        } catch (\Throwable $e) {
            $this->logError($e, $requestId, $httpResponse ?? null, $method, $url);

            throw $e;
        }

        $time = (int)(microtime(true) - $time);
        $this->logResponse($requestId, (string)$httpResponse->getBody(), $method, $url, $time);

        return $result;
    }

    private function buildUrl(string $type, string $code): string
    {
        return $this->config[$type]['host'] . $this->config[$type]['endpoints'][$code];
    }

    private function buildHeaders(string $token): array
    {
        return [
            'Content-Type'  => 'application/json',
            'Authorization' => $token,
        ];
    }
}
