<?php

declare(strict_types=1);

namespace ICEPAY\Checkout\Exceptions;

/**
 * Thrown when a postback's ICEPAY-Signature header does not match the request body,
 * meaning the request cannot be trusted as originating from ICEPAY.
 */
class InvalidSignature extends \RuntimeException
{
    public function __construct(string $message = 'The postback signature is invalid.')
    {
        parent::__construct($message);
    }
}
