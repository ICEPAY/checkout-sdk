# Usage

## Installations

To install the ICEPAY Checkout SDK, run the following command:

```shell
composer require icepay/checkout-sdk
```

The SDK requires implementations for PSR-7 (HTTP Message) and PSR-17 (HTTP Factory) interfaces. You can use any compatible libraries, such as Guzzle or Nyholm.

For example, to install the SDK along with Nyholm PSR-7 and Symfony HTTP Client, run:
```shell
composer require icepay/checkout-sdk nyholm/psr7 symfony/http-client
```

# Development

## Importing the Package Locally

```shell
composer install
```

## Running PHPUnit

```shell
composer phpunit
```
