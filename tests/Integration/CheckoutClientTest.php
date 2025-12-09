<?php

namespace ICEPAY\Tests\Integration;

use ICEPAY\Checkout\CheckoutClient;
use ICEPAY\Checkout\Models\Amount;
use ICEPAY\Checkout\Models\Request\Checkout;
use ICEPAY\Tests\TestCase;


require __DIR__ . '/../../vendor/autoload.php';

class CheckoutClientTest extends TestCase
{
    protected CheckoutClient $checkoutClient;
    protected function setUp(): void
    {
        parent::setUp();

        if (empty($_ENV['MERCHANT_ID']) || empty($_ENV['MERCHANT_SECRET'])) {
            $this->markTestSkipped('MERCHANT_ID or MERCHANT_SECRET not set in environment.');
        }

        $this->checkoutClient = (new CheckoutClient())->withAuthorization(
            merchantId: $_ENV['MERCHANT_ID'],
            merchantSecret: $_ENV['MERCHANT_SECRET'],
        );
    }


    public function testCheckoutCreation()
    {
        $reference = '#' . time();
        $checkoutRequest = new Checkout(
            reference: $reference,
            description: 'Test Checkout',
            amount: new Amount(1234, Amount::CURRENCY_EUR),
        );

        $response = $this->checkoutClient->createCheckout($checkoutRequest);
        $this->assertEquals($response->reference, $reference);
    }
}
