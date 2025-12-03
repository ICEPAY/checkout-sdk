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

## Basic Example
Here's a simple example of how to use the ICEPAY Checkout SDK:

```php
require 'vendor/autoload.php';
use ICEPAY\Checkout\CheckoutClient;
use ICEPAY\Checkout\Models\Amount;
use ICEPAY\Checkout\Models\Request\Checkout;

$checkoutClient = (new CheckoutClient())->withAuthorization(merchantId: 'your_merchant_id', merchantSecret: 'your_merchant_secret');
//$response = $checkoutClient->createPaymentIntent(amount: 1000, currency: 'EUR');
$reference = '#' . time();
$checkoutRequest = new Checkout(
    reference: $reference,
    description: 'Test Checkout',
    amount: new Amount(1234, Amount::CURRENCY_EUR),
);

$response = $this->checkoutClient->checkout($checkoutRequest);
print_r($response->links->checkout);
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
