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
use ICEPAY\Checkout\Models\Request\Checkout as CheckoutRequest;

$checkoutClient = (new CheckoutClient())->withAuthorization(merchantId: 'your_merchant_id', merchantSecret: 'your_merchant_secret');
$reference = '#' . time();
$checkoutRequest = new CheckoutRequest(
    reference: $reference,
    description: 'Test Checkout',
    amount: new Amount(1234, Amount::CURRENCY_EUR),
);

$response = $checkoutClient->createCheckout($checkoutRequest);
print_r($response->links->checkout);
```

## Providing a payment method
When the customer selects a payment method in your checkout, you can include it in the CheckoutRequest.  
```php
require 'vendor/autoload.php';
use ICEPAY\Checkout\CheckoutClient;
use ICEPAY\Checkout\Models\Amount;
use ICEPAY\Checkout\Models\Request\Checkout as CheckoutRequest;

$checkoutClient = (new CheckoutClient())->withAuthorization(merchantId: 'your_merchant_id', merchantSecret: 'your_merchant_secret');
$reference = '#' . time();
$checkoutRequest = new CheckoutRequest(
    reference: $reference,
    description: 'Test Checkout',
    amount: new Amount(1234, Amount::CURRENCY_EUR),
    paymentMethod: 'card',
);

$response = $checkoutClient->createCheckout($checkoutRequest);
print_r($response->links->direct);
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

        if (!in_array($request->getMethod(), ['GET', 'HEAD'])) {
            $args['body'] = $request->getBody();
        }

        $result = wp_remote_request($request->getUri(), $args);
        if (is_wp_error($result)) {
            throw new \RuntimeException('HTTP request failed: ' . $result->get_error_message());
        }

        return new Response($result['response']['code'], $result['headers'], $result['body']);
    }
}
```
You can then use this custom HTTP client with the CheckoutClient by wrapping it in the SDK's
`HttpClient` and passing that to the constructor:

```php
use ICEPAY\Checkout\CheckoutClient;
use ICEPAY\Checkout\HttpClient;

$httpClient = new HttpClient(client: new WpHttpWrapper());
$checkoutClient = (new CheckoutClient($httpClient))
    ->withAuthorization(merchantId: 'your_merchant_id', merchantSecret: 'your_merchant_secret');
```

## Error Handling

Every call on `CheckoutClient` (`createCheckout`, `getCheckout`, `refund`, `forward`, `getPaymentMethods`) throws `ICEPAY\Checkout\Exceptions\ApiException` for any failure, so a single catch covers the whole error model:

```php
use ICEPAY\Checkout\Exceptions\ApiException;

try {
    $response = $checkoutClient->createCheckout($checkoutRequest);
} catch (ApiException $e) {
    $e->getMessage(); // human-readable message
    $e->getCode();    // HTTP status code (0 for transport failures)
    $e->type;         // problem type, e.g. "icepay/problem/payment/validation"
    $e->errors;       // field-level validation errors, when present
}
```

This includes:

- **Typed API errors** such as `Exceptions\Payment\Validation` or `Exceptions\Payment\NotFound`, which extend `ApiException`. Catch a specific subclass to handle a particular case, or `ApiException` to handle them all.
- **Transport failures** (timeout, DNS, refused connection), which are thrown as `Exceptions\Connection` (also an `ApiException`) rather than a raw PSR-18 exception.

## Handling Postback Requests

After a payment status changes, ICEPAY sends a postback request to the provided webhookUrl. The `PostbackHandler` verifies the request's signature against your merchant secret and returns the parsed payment, so you don't have to recompute the HMAC yourself.

The primary API is framework-agnostic: pass the raw request body and the `ICEPAY-Signature` header value as strings. This works anywhere (plain PHP, WordPress/WooCommerce, Magento), since every framework can give you those two strings:

```php
use ICEPAY\Checkout\PostbackHandler;
use ICEPAY\Checkout\Exceptions\InvalidSignature;

$handler = new PostbackHandler(merchantSecret: 'your_merchant_secret_key');

$body = file_get_contents('php://input');
$signature = $_SERVER['HTTP_ICEPAY_SIGNATURE'] ?? '';

try {
    $payment = $handler->handle($body, $signature);
} catch (InvalidSignature) {
    // Invalid signature: reject the request and stop processing.
    http_response_code(400);
    return;
}

// Process the updated payment ($payment is a parsed Checkout) as needed
```

If you only need to check authenticity without parsing the payment, use `verify()`:

```php
if (!$handler->verify($body, $signature)) {
    http_response_code(400);
    return;
}
```

### PSR-7 requests

If you already have a PSR-7 `MessageInterface` (for example from a PSR-7 based framework), use `handleRequest()` / `verifyRequest()`, which read the body and `ICEPAY-Signature` header for you:

```php
use Psr\Http\Message\MessageInterface;

function postbackHandler(MessageInterface $request) use ($handler): void {
    try {
        $payment = $handler->handleRequest($request);
    } catch (InvalidSignature) {
        http_response_code(400);
        return;
    }

    // Process $payment
}
```


# Development

## Running PHPUnit

```shell
composer test
```

## Quick code example to get payment methods

To quickly test the code example to get payment methods, you can use the following script. Make sure to set your `MERCHANT_ID` and `MERCHANT_SECRET` in a `.env` file in the same directory.

```php
use Dotenv\Dotenv;
use ICEPAY\Checkout\CheckoutClient;

require_once "vendor/autoload.php";

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
$checkoutClient = (new CheckoutClient())->withAuthorization(merchantId:  $_ENV['MERCHANT_ID'], merchantSecret: $_ENV['MERCHANT_SECRET']);

$paymentMethods = $checkoutClient->getPaymentMethods();
var_dump($paymentMethods[0]->id);
```
