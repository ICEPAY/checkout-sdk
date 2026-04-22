<?php

namespace ICEPAY\Tests\Unit;

use ICEPAY\Checkout\Exceptions\Payment\NotFound;
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
        $this->assertEquals('card', $result[0]->id);
        $this->assertEquals('paypal', $result[1]->id);
    }

    public function testKnownErrorMapping()
    {
        $responseBody = [
            'type' => 'icepay/problem/payment/notFound',
            'status' => 404,
            'title' => 'Payment not found',
        ];
        $response = new Response(404, ['Content-Type' => 'application/json'], json_encode($responseBody));
        $checkoutClient = $this->getFixedResponseClient($response);

        $this->expectException(NotFound::class);
        $checkoutClient->getCheckout('pi-12345');
    }

    public function testDynamicErrorMapping()
    {
        $responseBody = [
            'type' => 'icepay/problem/payment/configuration',
            'status' => 400,
            'title' => 'Configuration error',
            'documentation' => ['https://docs.icepay.com'],
            'errors' => ['some' => 'error'],
            'trace' => 'trace-123',
        ];
        $response = new Response(400, ['Content-Type' => 'application/json'], json_encode($responseBody));
        $checkoutClient = $this->getFixedResponseClient($response);

        try {
            $checkoutClient->getCheckout('pi-12345');
            $this->fail('Expected exception was not thrown');
        } catch (\ICEPAY\Checkout\Exceptions\Payment\Configuration $e) {
            $this->assertEquals('Configuration error', $e->getMessage());
            $this->assertEquals(400, $e->getCode());
            $this->assertEquals('icepay/problem/payment/configuration', $e->type);
            $this->assertEquals(['https://docs.icepay.com'], $e->documentation);
            $this->assertEquals(['some' => 'error'], $e->errors);
            $this->assertEquals('trace-123', $e->trace);
        }
    }

    public function testUnknownErrorTypeDoesNotCrash()
    {
        $responseBody = [
            'type' => 'icepay/problem/unknown/error',
            'status' => 400,
            'title' => 'Some unknown error',
        ];
        $response = new Response(400, ['Content-Type' => 'application/json'], json_encode($responseBody));
        $checkoutClient = $this->getFixedResponseClient($response);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Some unknown error');
        $checkoutClient->getCheckout('pi-12345');
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
