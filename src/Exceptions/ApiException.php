<?php

declare(strict_types=1);

namespace ICEPAY\Checkout\Exceptions;

use Throwable;

class ApiException extends \Exception
{
    /**
     * @param array<string, mixed>|null $documentation
     * @param array<string, mixed>|null $errors
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?string $type = null,
        public readonly ?array $documentation = null,
        public readonly ?array $errors = null,
        public readonly ?string $trace = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
