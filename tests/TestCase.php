<?php

namespace ICEPAY\Tests;

use Dotenv\Dotenv;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Dotenv $dotenv;
    public function __construct(string $name)
    {
        parent::__construct($name);
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->safeLoad();
    }

}
