<?php

declare(strict_types=1);

namespace App\Services\System\Supervisor;
final readonly class Client
{
    private \CurlHandle $client;

    public function __construct(
        public string $user,
        public string $password,
        public string $host,
    )
    {
        $supervisorPort = 9001;
        $supervisorUrl = sprintf('http://%s:%d/RPC2', $host, $supervisorPort);

        $this->client = $this->xmlRpcClientCreate($supervisorUrl, $user, $password);
    }

    public function stopAllWorkers(): void
    {
        $this->xmlrpcCall('supervisor.stopAllProcesses');
    }

    public function startAllWorkers(): void
    {
        $this->xmlrpcCall('supervisor.startAllProcesses');
    }

    public function getWorkerList(): array
    {
        return $this->xmlrpcCall('supervisor.getAllProcessInfo');
    }

    public function startGroupWorkers(string $group): void
    {
        $this->xmlrpcCall('supervisor.startProcessGroup', [$group]);
    }

    public function stopGroupWorkers(string $group): void
    {
        $this->xmlrpcCall('supervisor.stopProcessGroup', [$group]);
    }

    private function xmlRpcClientCreate(string $url, string $username, string $password): \CurlHandle|false
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

        return $ch;
    }

    /**
     * Perform an XML-RPC call.
     */
    private function xmlrpcCall(string $method, array $params = []): array
    {
        $xml = $this->buildXmlRpcRequest($method, $params);

        curl_setopt_array($this->client, [
            CURLOPT_POSTFIELDS => $xml,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml',
                'Content-Length: ' . strlen($xml),
            ],
        ]);

        $response = curl_exec($this->client);

        if ($response === false) {
            return [
                'faultCode'   => -1,
                'faultString' => curl_error($this->client),
            ];
        }

        return $this->parseXmlRpcResponse($response);
    }

    private function buildXmlRpcRequest(string $method, array $params): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><methodCall></methodCall>');
        $xml->addChild('methodName', htmlspecialchars($method));

        if ($params) {
            $paramsNode = $xml->addChild('params');

            foreach ($params as $param) {
                $paramNode = $paramsNode->addChild('param');
                $valueNode = $paramNode->addChild('value');
                $this->encodeValue($valueNode, $param);
            }
        }

        return $xml->asXML();
    }

    private function encodeValue(\SimpleXMLElement $node, mixed $value): void
    {
        match (true) {
            is_int($value) => $node->addChild('int', (string)$value),
            is_bool($value) => $node->addChild('boolean', $value ? '1' : '0'),
            is_float($value) => $node->addChild('double', (string)$value),
            is_string($value) => $node->addChild('string', htmlspecialchars($value)),
            is_array($value) => $this->encodeArray($node, $value),
            default => throw new \InvalidArgumentException('Unsupported XML-RPC type'),
        };
    }

    private function encodeArray(\SimpleXMLElement $node, array $value): void
    {
        $arrayNode = $node->addChild('array')->addChild('data');

        foreach ($value as $item) {
            $valueNode = $arrayNode->addChild('value');
            $this->encodeValue($valueNode, $item);
        }
    }

    private function parseXmlRpcResponse(string $xml): array
    {
        $response = simplexml_load_string($xml);

        if (!$response) {
            return [
                'faultCode'   => -2,
                'faultString' => 'Invalid XML response',
            ];
        }

        if (isset($response->fault)) {
            return $this->decodeValue($response->fault->value);
        }

        return $this->decodeValue($response->params->param->value);
    }

    private function decodeValue(\SimpleXMLElement $value): mixed
    {
        if (isset($value->int)) return (int)$value->int;
        if (isset($value->i4)) return (int)$value->i4;
        if (isset($value->boolean)) return (bool)$value->boolean;
        if (isset($value->double)) return (float)$value->double;
        if (isset($value->string)) return (string)$value->string;

        if (isset($value->array)) {
            $result = [];
            foreach ($value->array->data->value as $item) {
                $result[] = $this->decodeValue($item);
            }
            return $result;
        }

        if (isset($value->struct)) {
            $result = [];
            foreach ($value->struct->member as $member) {
                $name = (string)$member->name;
                $result[$name] = $this->decodeValue($member->value);
            }
            return $result;
        }

        return null;
    }

}
