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

$response = $this->checkoutClient->checkout($checkoutRequest);
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

$response = $this->checkoutClient->checkout($checkoutRequest);
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

## Handling Postback Requests

After a payment status changes, ICEPAY sends a postback request to the provided webhookUrl. You can handle this request and verify its authenticity using the following example:

```php
use ICEPAY\Checkout\Models\Response\Checkout;
use Psr\Http\Message\MessageInterface;

function postbackHandler(MessageInterface $request): void {
    $providedSignature = $request->getHeader('ICEPAY-Signature');
    $body = $request->getBody()->getContents();
    $secretKey = 'your_merchant_secret_key'; // Replace with your actual secret key
    $calculatedSignature = base64_encode(hash_hmac('sha256', $body, $secretKey, true));
    if (!hash_equals($providedSignature[0], $calculatedSignature)) {
        // Invalid signature, reject the request
        http_response_code(400);
    }

    $payment = Checkout::fromResponse($body);
    // Process the updated payment data as needed
}
```


# Development

## Running PHPUnit

```shell
composer phpunit
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
