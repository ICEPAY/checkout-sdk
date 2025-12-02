<?php

namespace ICEPAY\Tests\Unit;

use ICEPAY\Checkout\CheckoutClient;
use ICEPAY\Checkout\HttpClient;
use ICEPAY\Checkout\Models\Amount;
use ICEPAY\Checkout\Models\Request\Checkout;
use ICEPAY\Checkout\Models\Status;
use ICEPAY\Tests\TestCase;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class EchoClient implements ClientInterface
{
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if($request->getUri()->getPath() === '/api/payments') {
            $data = json_decode((string)$request->getBody(), true);
            $data['key'] = 'pi-' . bin2hex(random_bytes(8));
            $data['status'] = Status::pending->toString();
            $body = json_encode($data);
            return new Response(200, ['Content-Type' => 'application/json'], $body);
        }

        return new Response(200, ['Content-Type' => 'application/json'], (string)$request->getBody());
    }
}
class CheckoutClientTest extends TestCase
{
    public function testCheckoutCreation()
    {
        $httpClient = new HttpClient(client: new EchoClient());
        $checkoutClient = (new CheckoutClient())->withHttpClient($httpClient);

        $reference = '#' . time();
        $checkoutRequest = new Checkout(
            reference: $reference,
            description: 'Test Checkout',
            amount: new Amount(1234, Amount::CURRENCY_EUR),
        );

        $response = $checkoutClient->checkout($checkoutRequest);
        $this->assertEquals($response->reference, $reference);
    }
}
