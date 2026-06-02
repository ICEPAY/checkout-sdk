<?php

namespace ICEPAY\Tests\Unit;

use ICEPAY\Checkout\Exceptions\ApiException;
use ICEPAY\Checkout\Exceptions\Connection;
use ICEPAY\Checkout\HttpClient;
use ICEPAY\Tests\Support\FakeClient;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClientTest extends \PHPUnit\Framework\TestCase
{
    public function testTransportFailureIsWrappedInAConnectionException(): void
    {
        $throwingClient = new class implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw new class ('Connection refused') extends \RuntimeException implements ClientExceptionInterface {
                };
            }
        };

        $client = new HttpClient(client: $throwingClient);

        try {
            $client->get('https://example.com/resource');
            $this->fail('Expected a Connection exception');
        } catch (Connection $e) {
            $this->assertInstanceOf(ApiException::class, $e);
            $this->assertStringContainsString('Connection refused', $e->getMessage());
        }
    }

    public function testPostJsonCreatesProperRequest(): void
    {
        $fake = new FakeClient();
        $fake->queueJson(200, ['ok' => true]);

        $client = new HttpClient(client: $fake);
        $client->withAuthorization('testid', 'testkey');
        $response = $client->post('https://example.com/test', ['foo' => 'bar']);

        $this->assertSame(200, $response->getStatusCode());

        $request = $fake->getLastRequest();
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://example.com/test', (string) $request->getUri());
        $this->assertSame(['application/json'], $request->getHeader('Content-Type'));
        $this->assertSame(['application/json'], $request->getHeader('Accept'));
        $this->assertSame(['Basic dGVzdGlkOnRlc3RrZXk='], $request->getHeader('Authorization'));
        $this->assertSame('{"foo":"bar"}', (string) $request->getBody());

        $decoded = $client->decodeJson($response);
        $this->assertSame(['ok' => true], $decoded);
    }

    public function testGetCreatesProperRequest(): void
    {
        $fake = new FakeClient();
        $fake->queueJson(200, ['items' => []]);

        $client = new HttpClient(client: $fake);
        $client->withAuthorization('merchant', 'secret');
        $response = $client->get('https://example.com/resource');

        $this->assertSame(200, $response->getStatusCode());

        $request = $fake->getLastRequest();
        $this->assertNotNull($request);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://example.com/resource', (string) $request->getUri());
        $this->assertSame(['application/json'], $request->getHeader('Accept'));
        $this->assertNotEmpty($request->getHeader('Authorization'));
    }

    public function testMultipleRequestsAreAllRecorded(): void
    {
        $fake = new FakeClient();
        $fake->queueJson(200, ['a' => 1]);
        $fake->queueJson(201, ['b' => 2]);

        $client = new HttpClient(client: $fake);
        $client->post('https://example.com/one', ['a' => 1]);
        $client->get('https://example.com/two');

        $requests = $fake->getRequests();
        $this->assertCount(2, $requests);
        $this->assertSame('POST', $requests[0]->getMethod());
        $this->assertSame('GET', $requests[1]->getMethod());
    }

    public function testFallsBackToEmptyResponseWhenQueueIsExhausted(): void
    {
        $fake = new FakeClient();
        $client = new HttpClient(client: $fake);

        $response = $client->get('https://example.com/test');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $client->decodeJson($response));
    }
}
