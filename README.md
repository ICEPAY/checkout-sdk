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
$reference = '#' . time();
$checkoutRequest = new createCheckout(
    reference: $reference,
    description: 'Test Checkout',
    amount: new Amount(1234, Amount::CURRENCY_EUR),
);

$response = $this->checkoutClient->checkout($checkoutRequest);
print_r($response->links->checkout);
```

## Using a custom HTTP Client
You can use your own PSR-7 and PSR-17 compatible HTTP client with the SDK. Here's an example client that wraps proprietary HTTP client logic:

```php
<?php

use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WpHttpWrapper implements ClientInterface
{
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $args = [
            'method' => $request->getMethod(),
            'headers' => $request->getHeaders(),
		];

        if(!in_array($request->getMethod(), ['GET', 'HEAD'])){
            $args['body'] = $request->getBody();
        }

        $result = wp_remote_request($request->getUri(), $args);
        if(!is_wp_error($result))
        {
            throw new HttpException('HTTP request failed: ' . $result->get_error_message());
        }

        return new Response($result['response']['code'], $result['headers'], $result['body']);
    }
}
```
You can then use this custom HTTP client with the CheckoutClient like so:

```php
$httpClient = new WpHttpWrapper();
$checkoutClient = (new CheckoutClient())
    ->withHttpClient($httpClient)
    ->withAuthorization(merchantId: 'your_merchant_id', merchantSecret: 'your_merchant_secret');
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
