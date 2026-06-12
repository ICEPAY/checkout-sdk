<?php

namespace ICEPAY\Tests\Unit;

use ICEPAY\Checkout\Exceptions\InvalidSignature;
use ICEPAY\Checkout\Models\Response\Checkout;
use ICEPAY\Checkout\PostbackHandler;
use ICEPAY\Tests\TestCase;
use Nyholm\Psr7\Request;

class PostbackHandlerTest extends TestCase
{
    private const MERCHANT_SECRET = 'merchant-secret';

    private function postbackBody(): string
    {
        return json_encode([
            'key'         => 'pi-124567890abcdef',
            'status'      => 'completed',
            'amount'      => ['value' => 1234, 'currency' => 'EUR'],
            'reference'   => 'ref-001',
            'description' => 'Test Checkout',
        ]);
    }

    private function sign(string $body): string
    {
        return base64_encode(hash_hmac('sha256', $body, self::MERCHANT_SECRET, true));
    }

    public function testVerifyReturnsTrueForValidSignature(): void
    {
        $body = $this->postbackBody();
        $handler = new PostbackHandler(self::MERCHANT_SECRET);

        $this->assertTrue($handler->verify($body, $this->sign($body)));
    }

    public function testVerifyReturnsFalseForTamperedBody(): void
    {
        $body = $this->postbackBody();
        $signature = $this->sign($body);
        $handler = new PostbackHandler(self::MERCHANT_SECRET);

        $tampered = str_replace('1234', '9999', $body);

        $this->assertFalse($handler->verify($tampered, $signature));
    }

    public function testVerifyReturnsFalseForEmptySignature(): void
    {
        $body = $this->postbackBody();
        $handler = new PostbackHandler(self::MERCHANT_SECRET);

        $this->assertFalse($handler->verify($body, ''));
    }

    public function testHandleReturnsParsedCheckoutForValidSignature(): void
    {
        $body = $this->postbackBody();
        $handler = new PostbackHandler(self::MERCHANT_SECRET);

        $payment = $handler->handle($body, $this->sign($body));

        $this->assertInstanceOf(Checkout::class, $payment);
        $this->assertSame('pi-124567890abcdef', $payment->key);
        $this->assertSame('ref-001', $payment->reference);
    }

    public function testHandleThrowsForInvalidSignature(): void
    {
        $body = $this->postbackBody();
        $handler = new PostbackHandler(self::MERCHANT_SECRET);

        $this->expectException(InvalidSignature::class);
        $handler->handle($body, 'not-the-real-signature');
    }

    public function testVerifyRequestReadsBodyAndSignatureHeader(): void
    {
        $body = $this->postbackBody();
        $handler = new PostbackHandler(self::MERCHANT_SECRET);
        $request = new Request('POST', '/webhook', ['ICEPAY-Signature' => $this->sign($body)], $body);

        $this->assertTrue($handler->verifyRequest($request));
    }

    public function testVerifyRequestReturnsFalseWhenSignatureHeaderMissing(): void
    {
        $body = $this->postbackBody();
        $handler = new PostbackHandler(self::MERCHANT_SECRET);
        $request = new Request('POST', '/webhook', [], $body);

        $this->assertFalse($handler->verifyRequest($request));
    }

    public function testHandleRequestReturnsParsedCheckout(): void
    {
        $body = $this->postbackBody();
        $handler = new PostbackHandler(self::MERCHANT_SECRET);
        $request = new Request('POST', '/webhook', ['ICEPAY-Signature' => $this->sign($body)], $body);

        $payment = $handler->handleRequest($request);

        $this->assertInstanceOf(Checkout::class, $payment);
        $this->assertSame('pi-124567890abcdef', $payment->key);
    }
}
