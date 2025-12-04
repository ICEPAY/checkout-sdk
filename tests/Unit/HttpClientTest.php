<?php

namespace ICEPAY\Tests\Unit;

use ICEPAY\Checkout\HttpClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DummyClient implements ClientInterface
{
    public ?RequestInterface $lastRequest = null;

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->lastRequest = $request;
        return new Response(200, ['Content-Type' => 'application/json'], '{"ok":true}');
    }
}

class HttpClientTest extends \PHPUnit\Framework\TestCase
{
    public function testPostJsonCreatesProperRequest()
    {
        $dummy = new DummyClient();
        $factory = new Psr17Factory();
        $client = new HttpClient($dummy, $factory, $factory);
        $client->withAuthorization('testid','testkey');
        $response = $client->post('https://example.com/test', ['foo' => 'bar']);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($dummy->lastRequest);
        $this->assertSame('POST', $dummy->lastRequest->getMethod());
        $this->assertSame('https://example.com/test', (string)$dummy->lastRequest->getUri());
        $this->assertSame(['application/json'], $dummy->lastRequest->getHeader('Content-Type'));
        $this->assertSame(['application/json'], $dummy->lastRequest->getHeader('Accept'));
        $this->assertSame(['Basic dGVzdGlkOnRlc3RrZXk='], $dummy->lastRequest->getHeader('Authorization'));
        $this->assertSame('{"foo":"bar"}', (string)$dummy->lastRequest->getBody());

        $decoded = $client->decodeJson($response);
        $this->assertSame(['ok' => true], $decoded);
    }
}

