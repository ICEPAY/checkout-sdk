<?php

namespace ICEPAY\Checkout;

use ICEPAY\Checkout\Models\JsonDeserializable;
use ICEPAY\Checkout\Models\Request\Checkout as CheckoutRequest;
use ICEPAY\Checkout\Models\Request\Forward as ForwardRequest;
use ICEPAY\Checkout\Models\Request\Refund as RefundRequest;
use ICEPAY\Checkout\Models\Response\Checkout as CheckoutResponse;
use ICEPAY\Checkout\Models\Response\Forward as ForwardResponse;
use ICEPAY\Checkout\Models\Response\PaymentMethod;
use ICEPAY\Checkout\Models\Response\Refund as RefundResponse;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;

class CheckoutClient
{
    const BASE_URL = 'https://checkout.icepay.com/';

    public function __construct(protected HttpClient $httpClient = new HttpClient())
    {
    }

    public function withAuthorization(string $merchantId, string $merchantSecret): self
    {
        $this->httpClient->withAuthorization($merchantId, $merchantSecret);
        return $this;
    }

    public function withHttpClient(HttpClient $httpClient): self
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    // POST: https://checkout.icepay.com/api/payments
    public function createCheckout(CheckoutRequest $checkout): CheckoutResponse
    {
        return $this->callCheckoutApi(self::BASE_URL . 'api/payments', CheckoutResponse::class, $checkout);
    }

    // POST https://checkout.icepay.com/api/payments/{id}/refund
    public function refund(RefundRequest $refund, string $checkoutId): RefundResponse
    {
        return $this->callCheckoutApi(self::BASE_URL . 'api/payments/' . $checkoutId . '/refund', RefundResponse::class, $refund);
    }

    // POST https://checkout.icepay.com/api/payments/{id}/forward
    public function forward(ForwardRequest $forward, string $checkoutId): ForwardResponse
    {
        return $this->callCheckoutApi(self::BASE_URL . 'api/payments/' . $checkoutId . '/forward', ForwardResponse::class, $forward);
    }

    // GET: https://checkout.icepay.com/api/payments/{key}
    public function getCheckout(string $checkoutId): CheckoutResponse
    {
        return $this->callCheckoutApi(self::BASE_URL . 'api/payments/' . $checkoutId, CheckoutResponse::class);
    }

    // GET: https://checkout.icepay.com/api/payments/methods
    public function getPaymentMethods(): array
    {
        $response = $this->httpClient->get(self::BASE_URL . 'api/payments/methods');
        $this->checkStatusCode($response);
        $json = $response->getBody()->__toString();

        return array_map(static function (array $methodData): PaymentMethod {
            return PaymentMethod::fromArray($methodData);
        }, json_decode($json, true));
    }

    /**
     * @template ResponseType of JsonDeserializable
     * @param string $url
     * @param class-string<ResponseType> $className
     * @param JsonSerializable|null $payload
     * @return ResponseType
     * @throws \Exception
     */
    protected function callCheckoutApi(string $url, string $className, ?JsonSerializable $payload = null): JsonDeserializable
    {
        if (!is_subclass_of($className, JsonDeserializable::class)) {
            throw new \Exception("Class $className is not a subclass of JsonDeserializable");
        }
        if ($payload !== null) {
            $response = $this->httpClient->post($url, $payload);
        } else {
            $response = $this->httpClient->get($url);
        }
        $this->checkStatusCode($response);
        $json = $response->getBody()->__toString();

        return $className::fromResponse($json);
    }

    protected function checkStatusCode(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 200 && $statusCode < 300) {
            return true;
        }
        throw new \Exception("Request failed with status code: " . $statusCode);
    }
}
