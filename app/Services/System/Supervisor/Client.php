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
        $request = xmlrpc_encode_request($method, $params);
        curl_setopt($this->client, CURLOPT_POSTFIELDS, $request);
        curl_setopt($this->client, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);

        $response = curl_exec($this->client);

        if ($response === false) {
            return ['faultCode' => -1, 'faultString' => curl_error($this->client)];
        }

        $decoded = xmlrpc_decode($response);

        return $decoded ?: ['faultCode' => -2, 'faultString' => 'Invalid response'];
    }
}