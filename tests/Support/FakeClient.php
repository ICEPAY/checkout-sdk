<?php

namespace ICEPAY\Tests\Support;

use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-18 client that records outgoing requests and returns queued responses.
 * Falls back to an empty 200 JSON response when the queue is exhausted.
 */
class FakeClient implements ClientInterface
{
    /** @var ResponseInterface[] */
    private array $responseQueue = [];

    /** @var RequestInterface[] */
    private array $recordedRequests = [];

    public function queue(ResponseInterface $response): self
    {
        $this->responseQueue[] = $response;
        return $this;
    }

    public function queueJson(int $status, array $body, array $headers = []): self
    {
        return $this->queue(new Response(
            $status,
            array_merge(['Content-Type' => 'application/json'], $headers),
            json_encode($body)
        ));
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->recordedRequests[] = $request;

        if (!empty($this->responseQueue)) {
            return array_shift($this->responseQueue);
        }

        return new Response(200, ['Content-Type' => 'application/json'], '{}');
    }

    public function getLastRequest(): ?RequestInterface
    {
        return !empty($this->recordedRequests) ? end($this->recordedRequests) : null;
    }

    public function getRequest(int $index = 0): ?RequestInterface
    {
        return $this->recordedRequests[$index] ?? null;
    }

    /** @return RequestInterface[] */
    public function getRequests(): array
    {
        return $this->recordedRequests;
    }

    public function getLastRequestBody(): array
    {
        $request = $this->getLastRequest();
        if ($request === null) {
            return [];
        }
        $body = (string) $request->getBody();
        return $body !== '' ? (json_decode($body, true) ?? []) : [];
    }

}
