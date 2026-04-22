<?php

namespace ICEPAY\Checkout\Exceptions;

use Throwable;

class ApiException extends \Exception
{
    public function __construct(string $message = "",
                                int $code = 0,
                                ?Throwable $previous = null,
                                public readonly ?string $type = null,
                                public readonly ?array $documentation = null,
                                public readonly ?array $errors = null,
                                public readonly ?string $trace = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
