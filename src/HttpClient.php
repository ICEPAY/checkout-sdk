<?php

declare(strict_types=1);

namespace ICEPAY\Checkout;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use ICEPAY\Checkout\Exceptions\Connection;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class HttpClient
{
    protected ClientInterface $client;
    protected RequestFactoryInterface $requestFactory;
    protected StreamFactoryInterface $streamFactory;

    /** @var array<string, string> */
    protected array $defaultHeaders = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];

    public function __construct(
        ?ClientInterface $client = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ) {
        $this->client = $client ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
    }

    public function withHeader(string $name, string $value): self
    {
        $this->defaultHeaders[$name] = $value;
        return $this;
    }

    public function withAuthorization(string $merchantId, string $merchantSecret): self
    {
        $this->defaultHeaders['Authorization'] = 'Basic ' . base64_encode($merchantId . ':' . $merchantSecret);
        return $this;
    }

    /**
     * Send a POST request with a JSON payload.
     *
     * @param array<string, string|null> $headers
     */
    public function post(string $url, mixed $payload, array $headers = []): ResponseInterface
    {
        $json = $this->encodeJson($payload);
        $body = $this->streamFactory->createStream($json);
        $request = $this->requestFactory->createRequest('POST', $url);

        $mergedHeaders = array_merge($this->defaultHeaders, $headers);
        foreach ($mergedHeaders as $name => $value) {
            if ($value !== null) {
                $request = $request->withHeader($name, $value);
            }
        }
        $request = $request->withBody($body);

        return $this->send($request);
    }

    /** @param array<string, string|null> $headers */
    public function get(string $string, array $headers = []): ResponseInterface
    {
        $request = $this->requestFactory->createRequest('GET', $string);

        $mergedHeaders = array_merge($this->defaultHeaders, $headers);
        foreach ($mergedHeaders as $name => $value) {
            if ($value !== null) {
                $request = $request->withHeader($name, $value);
            }
        }

        return $this->send($request);
    }

    /**
     * Generic send wrapper.
     *
     * @throws Connection When the request fails at the transport layer (timeout, DNS, refused connection).
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new Connection('HTTP request to ICEPAY failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Decode a JSON response body into an array.
     *
     * @return array<int|string, mixed>
     * @throws \JsonException When the body is not valid JSON.
     */
    public function decodeJson(ResponseInterface|string $response): array
    {
        return Json::decode(is_string($response) ? $response : (string) $response->getBody());
    }

    protected function encodeJson(mixed $payload): string
    {
        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to encode JSON payload: ' . $e->getMessage(), 0, $e);
        }
    }
}
