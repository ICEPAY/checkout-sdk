<?php

namespace ICEPAY\Tests\Unit;

use ICEPAY\Checkout\CheckoutClient;
use ICEPAY\Checkout\HttpClient;
use ICEPAY\Checkout\Models\Amount;
use ICEPAY\Checkout\Models\Request\Checkout;
use ICEPAY\Checkout\Models\PaymentMethod;
use ICEPAY\Checkout\Models\Request\Refund;
use ICEPAY\Checkout\Models\Status;
use ICEPAY\Tests\Support\FakeClient;
use ICEPAY\Tests\TestCase;
use Nyholm\Psr7\Response;

class CheckoutClientTest extends TestCase
{
    // --- Helpers ---

    private function minimalCheckoutResponse(string $reference = 'ref-001'): array
    {
        return [
            'key'         => 'pi-124567890abcdef',
            'status'      => Status::pending->toString(),
            'amount'      => ['value' => 1234, 'currency' => Amount::CURRENCY_EUR],
            'reference'   => $reference,
            'description' => 'Test Checkout',
        ];
    }

    /** @return array{FakeClient, CheckoutClient} */
    private function makeClient(array ...$queuedBodies): array
    {
        $fake = new FakeClient();
        foreach ($queuedBodies as $body) {
            $fake->queueJson(200, $body);
        }
        $client = (new CheckoutClient())->withHttpClient(new HttpClient(client: $fake));
        return [$fake, $client];
    }

    // --- Response parsing tests ---

    public function testCheckoutCreationParsesResponse(): void
    {
        $reference = '#' . time();
        [, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse($reference));

        $checkoutRequest = new Checkout(
            reference: $reference,
            description: 'Test Checkout',
            amount: new Amount(1234, Amount::CURRENCY_EUR),
        );

        $response = $checkoutClient->createCheckout($checkoutRequest);

        $this->assertEquals($reference, $response->reference);
    }

    public function testGetPaymentMethodsParsesResponse(): void
    {
        $methods = [
            ['id' => 'card',   'description' => 'Card'],
            ['id' => 'paypal', 'description' => 'PayPal'],
        ];
        [, $checkoutClient] = $this->makeClient($methods);

        $result = $checkoutClient->getPaymentMethods();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('card',   $result[0]->id);
        $this->assertEquals('paypal', $result[1]->id);
    }

    // --- Request inspection tests ---

    public function testCreateCheckoutSendsPostToCorrectUrl(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkoutClient->createCheckout(new Checkout(
            reference: 'ref-001',
            description: 'Test',
            amount: new Amount(1234, Amount::CURRENCY_EUR),
        ));

        $request = $fake->getLastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://checkout.icepay.com/api/payments', (string) $request->getUri());
    }

    public function testCreateCheckoutSendsCorrectBody(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkoutRequest = new Checkout(
            reference: 'order-42',
            description: 'Test payment',
            amount: new Amount(9900, Amount::CURRENCY_EUR),
        );

        $checkoutClient->createCheckout($checkoutRequest);

        $body = $fake->getLastRequestBody();
        $this->assertSame('order-42',           $body['reference']);
        $this->assertSame('Test payment',       $body['description']);
        $this->assertSame(9900,                 $body['amount']['value']);
        $this->assertSame(Amount::CURRENCY_EUR, $body['amount']['currency']);
    }

    public function testGetCheckoutSendsGetToCorrectUrl(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkoutClient->getCheckout('pi-abc123');

        $request = $fake->getLastRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://checkout.icepay.com/api/payments/pi-abc123', (string) $request->getUri());
    }

    public function testGetPaymentMethodsSendsGetToCorrectUrl(): void
    {
        [$fake, $checkoutClient] = $this->makeClient([['id' => 'card', 'description' => 'Card']]);

        $checkoutClient->getPaymentMethods();

        $request = $fake->getLastRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://checkout.icepay.com/api/payments/methods', (string) $request->getUri());
    }

    public function testRefundSendsPostToCorrectUrl(): void
    {
        $refundResponse = [
            'key'         => 'ref-abc123',
            'status'      => Status::pending->toString(),
            'amount'      => ['value' => 500, 'currency' => Amount::CURRENCY_EUR],
            'reference'   => 'ref-001',
            'description' => 'Refund',
        ];
        [$fake, $checkoutClient] = $this->makeClient($refundResponse);

        $checkoutClient->refund(
            new Refund('ref-001', new Amount(500, Amount::CURRENCY_EUR), 'Refund'),
            'pi-abc123'
        );

        $request = $fake->getLastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://checkout.icepay.com/api/payments/pi-abc123/refund', (string) $request->getUri());
    }

    public function testRefundSendsCorrectBody(): void
    {
        $refundResponse = [
            'key'         => 'ref-abc123',
            'status'      => Status::pending->toString(),
            'amount'      => ['value' => 500, 'currency' => Amount::CURRENCY_EUR],
            'reference'   => 'ref-001',
            'description' => 'Test refund',
        ];
        [$fake, $checkoutClient] = $this->makeClient($refundResponse);

        $checkoutClient->refund(
            new Refund('ref-001', new Amount(500, Amount::CURRENCY_EUR), 'Test refund'),
            'pi-abc123'
        );

        $body = $fake->getLastRequestBody();
        $this->assertSame('ref-001',     $body['reference']);
        $this->assertSame(500,           $body['amount']['value']);
        $this->assertSame('Test refund', $body['description']);
    }

    public function testAuthorizationHeaderIsSent(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());
        $checkoutClient->withAuthorization('merchant-123', 'secret-abc');

        $checkoutRequest = new Checkout(
            reference: 'ref-001',
            description: 'Test',
            amount: new Amount(1234, Amount::CURRENCY_EUR),
        );

        $checkoutClient->createCheckout($checkoutRequest);

        $request = $fake->getLastRequest();
        $expected = 'Basic ' . base64_encode('merchant-123:secret-abc');
        $this->assertSame([$expected], $request->getHeader('Authorization'));
    }

    public function testWithReferenceIsIncludedInBody(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkoutRequest = (
            new Checkout(
                amount: new Amount(1234, Amount::CURRENCY_EUR)
            )
        )->withReference('order-99');

        $checkoutClient->createCheckout(
            $checkoutRequest
        );

        $this->assertSame('order-99', $fake->getLastRequestBody()['reference']);
    }

    public function testWithDescriptionIsIncludedInBody(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkoutRequest = (
            new Checkout(
                amount: new Amount(1234, Amount::CURRENCY_EUR)
            )
        )->withDescription('My test order');

        $checkoutClient->createCheckout($checkoutRequest);

        $this->assertSame('My test order', $fake->getLastRequestBody()['description']);
    }

    public function testWithRedirectUrlIsIncludedInBody(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkoutRequest = (
            new Checkout(
                amount: new Amount(1234, Amount::CURRENCY_EUR)
            )
        )->withRedirectUrl('https://example.com/return');

        $checkoutClient->createCheckout($checkoutRequest);

        $this->assertSame('https://example.com/return', $fake->getLastRequestBody()['redirectUrl']);
    }

    public function testWithWebhookUrlIsIncludedInBody(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkoutRequest = (
            new Checkout(
                amount: new Amount(1234, Amount::CURRENCY_EUR)
            )
        )->withWebhookUrl('https://example.com/webhook');

        $checkoutClient->createCheckout($checkoutRequest);

        $this->assertSame('https://example.com/webhook', $fake->getLastRequestBody()['webhookUrl']);
    }

    public function testWithCustomerIsIncludedInBody(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkoutRequest = (
            new Checkout(
                amount: new Amount(1234, Amount::CURRENCY_EUR)
            )
        )->withCustomer(['name' => 'John Doe', 'email' => 'john@example.com']);

        $checkoutClient->createCheckout($checkoutRequest);

        $body = $fake->getLastRequestBody();
        $this->assertSame('John Doe',          $body['metadata']['customer']['name']);
        $this->assertSame('john@example.com',  $body['metadata']['customer']['email']);
    }

    public function testExpireAfterIsIncludedInBody(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkoutRequest = new Checkout(
            amount: new Amount(1234, Amount::CURRENCY_EUR),
            expireAfter: 3600
        );

        $checkoutClient->createCheckout($checkoutRequest);

        $this->assertSame(3600, $fake->getLastRequestBody()['expireAfter']);
    }

    public function testPaymentMethodAsStringIsIncludedInBody(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkoutRequest = new Checkout(
            amount: new Amount(1234, Amount::CURRENCY_EUR),
            paymentMethod: PaymentMethod::TYPE_IDEAL
        );

        $checkoutClient->createCheckout($checkoutRequest);

        $this->assertSame(PaymentMethod::TYPE_IDEAL, $fake->getLastRequestBody()['paymentMethod']['type']);
    }

    public function testPaymentMethodObjectWithIssuerIsIncludedInBody(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkout = new Checkout(
            amount: new Amount(1234, Amount::CURRENCY_EUR),
            paymentMethod: new PaymentMethod(PaymentMethod::TYPE_IDEAL, 'INGBNL2A'),
        );

        $checkoutClient->createCheckout($checkout);

        $body = $fake->getLastRequestBody();
        $this->assertSame(PaymentMethod::TYPE_IDEAL, $body['paymentMethod']['type']);
        $this->assertSame('INGBNL2A',                $body['paymentMethod']['issuer']);
    }

    public function testExpireAfterIsAbsentFromBodyWhenNotSet(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkoutRequest = new Checkout(
            amount: new Amount(1234, Amount::CURRENCY_EUR)
        );

        $checkoutClient->createCheckout($checkoutRequest);

        $this->assertArrayNotHasKey('expireAfter', $fake->getLastRequestBody());
    }

    public function testPaymentMethodIsAbsentFromBodyWhenNotSet(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkout = new Checkout(
            amount: new Amount(1234, Amount::CURRENCY_EUR)
        );

        $checkoutClient->createCheckout($checkout);

        $this->assertArrayNotHasKey('paymentMethod', $fake->getLastRequestBody());
    }

    public function testPaymentMethodObjectWithoutIssuerOmitsIssuerFromBody(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkoutRequest = new Checkout(
            amount: new Amount(1234, Amount::CURRENCY_EUR),
            paymentMethod: new PaymentMethod(PaymentMethod::TYPE_IDEAL),
        );

        $checkoutClient->createCheckout($checkoutRequest);

        $body = $fake->getLastRequestBody();
        $this->assertSame(PaymentMethod::TYPE_IDEAL, $body['paymentMethod']['type']);
        $this->assertArrayNotHasKey('issuer', $body['paymentMethod']);
    }

    public function testCustomerEmailIsIncludedInBody(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkout = (
            new Checkout(
                reference: 'ref-001',
                description: 'Test',
                amount: new Amount(1234, Amount::CURRENCY_EUR)
            )
        )->withCustomerEmail('customer@example.com');

        $checkoutClient->createCheckout($checkout);

        $body = $fake->getLastRequestBody();
        $this->assertSame('customer@example.com', $body['metadata']['customer']['email']);
    }

    public function testIntegrationInformationIsIncludedInBody(): void
    {
        [$fake, $checkoutClient] = $this->makeClient($this->minimalCheckoutResponse());

        $checkoutRequest = (
            new Checkout(
                reference: 'ref-001',
                description: 'Test',
                amount: new Amount(1234, Amount::CURRENCY_EUR)
            )
        )->withIntegrationInformation('WooCommerce', '1.0.0', 'Acme');

        $checkoutClient->createCheckout($checkoutRequest);

        $body = $fake->getLastRequestBody();
        $this->assertSame('WooCommerce', $body['metadata']['integration']['type']);
        $this->assertSame('1.0.0',       $body['metadata']['integration']['version']);
        $this->assertSame('Acme',        $body['metadata']['integration']['developer']);
    }

    public function testNonSuccessStatusCodeThrowsException(): void
    {
        $fake = new FakeClient();
        $fake->queue(new Response(422, ['Content-Type' => 'application/json'], '{"error":"invalid"}'));
        $checkoutClient = new CheckoutClient(new HttpClient(client: $fake));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/422/');

        $checkoutRequest = new Checkout(
            reference: 'ref-001',
            description: 'Test',
            amount: new Amount(1234, Amount::CURRENCY_EUR),
        );

        $checkoutClient->createCheckout($checkoutRequest);
    }
}
