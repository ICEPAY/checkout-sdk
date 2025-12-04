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


class CheckoutClientTest extends TestCase
{
    public function testCheckoutCreation()
    {
        $reference = '#' . time();

        $responseBody = [
            'key' => 'pi-124567890abcdef',
            'status' => Status::pending->toString(),
            'amount' => [
                'value' => 1234,
                'currency' => Amount::CURRENCY_EUR,
            ],
            'reference' => $reference,
            'description' => 'Test Checkout',
        ];
        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($responseBody));
        $checkoutClient = $this->getFixedResponseClient($response);


        $checkoutRequest = new Checkout(
            reference: $reference,
            description: 'Test Checkout',
            amount: new Amount(1234, Amount::CURRENCY_EUR),
        );

        $response = $checkoutClient->createCheckout($checkoutRequest);
        $this->assertEquals($response->reference, $reference);
    }

    public function testGetPaymentMethods()
    {
        $methods = [
            ['id' => 'card', 'description' => 'Card'],
            ['id' => 'paypal', 'description' => 'PayPal'],
        ];
        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($methods));
        $checkoutClient = $this->getFixedResponseClient($response);

        $result = $checkoutClient->getPaymentMethods();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('card', $result[0]['id']);
        $this->assertEquals('paypal', $result[1]['id']);
    }

    protected function getFixedResponseClient(Response $response): CheckoutClient
    {
        $httpClient = new class($response) implements ClientInterface {
            public function __construct(protected Response $response)
            {
            }
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };

        return (new CheckoutClient())->withHttpClient(new HttpClient(client: $httpClient));
    }
}
