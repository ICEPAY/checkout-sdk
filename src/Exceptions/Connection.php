<?php

declare(strict_types=1);

namespace ICEPAY\Checkout\Exceptions;

/**
 * Thrown when the request never reaches a usable HTTP response: connection refused,
 * DNS failure, timeout, or any other PSR-18 transport error.
 */
class Connection extends ApiException
{
}
