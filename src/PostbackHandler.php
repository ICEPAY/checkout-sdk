<?php

declare(strict_types=1);

namespace ICEPAY\Checkout;

use ICEPAY\Checkout\Exceptions\InvalidSignature;
use ICEPAY\Checkout\Models\Response\Checkout;
use Psr\Http\Message\MessageInterface;

final class PostbackHandler
{
    private const SIGNATURE_HEADER = 'ICEPAY-Signature';

    public function __construct(private readonly string $merchantSecret)
    {
    }

    public function verify(string $body, string $signature): bool
    {
        $calculatedSignature = base64_encode(hash_hmac('sha256', $body, $this->merchantSecret, true));

        return hash_equals($calculatedSignature, $signature);
    }

    /**
     * Verify the postback's signature and return the parsed payment.
     *
     * @throws InvalidSignature When the signature does not match the body.
     * @throws \RuntimeException When the body is not valid JSON.
     */
    public function handle(string $body, string $signature): Checkout
    {
        if (!$this->verify($body, $signature)) {
            throw new InvalidSignature();
        }

        return Checkout::fromResponse($body);
    }

    public function verifyRequest(MessageInterface $request): bool
    {
        return $this->verify((string) $request->getBody(), $request->getHeaderLine(self::SIGNATURE_HEADER));
    }

    /**
     * @throws InvalidSignature When the signature does not match the body.
     * @throws \RuntimeException When the body is not valid JSON.
     */
    public function handleRequest(MessageInterface $request): Checkout
    {
        return $this->handle((string) $request->getBody(), $request->getHeaderLine(self::SIGNATURE_HEADER));
    }
}
