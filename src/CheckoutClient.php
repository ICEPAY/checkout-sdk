<?php

namespace ICEPAY\Checkout;

use ICEPAY\Checkout\Models\JsonDeserializable;
use ICEPAY\Checkout\Models\Request\Checkout as CheckoutRequest;
use ICEPAY\Checkout\Models\Response\Checkout as CheckoutResponse;
use ICEPAY\Checkout\Models\Request\Refund as RefundRequest;
use ICEPAY\Checkout\Models\Response\Refund as RefundResponse;
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
        /** @var CheckoutResponse $result */
        $result = $this->callCheckoutApi(self::BASE_URL . 'api/payments', CheckoutResponse::class, $checkout);
        return $result;


//        $response = $this->httpClient->post(self::BASE_URL . 'api/payments', $checkout);
//        $this->checkStatusCode($response);
//        $json = $response->getBody()->__toString();
//        $checkoutResponse = CheckoutResponse::fromResponse($json);
//        return $checkoutResponse;
    }

    // POST https://checkout.icepay.com/api/payments/{id}/refund
    public function refund(RefundRequest $refund, string $checkoutId) : RefundResponse
    {
        $response = $this->httpClient->post(self::BASE_URL . 'api/payments/' . $checkoutId . '/refund', $refund);
        $this->checkStatusCode($response);
        $json = $response->getBody()->__toString();
        $refundResponse = RefundResponse::fromResponse($json);
        return $refundResponse;
    }

    // GET: https://checkout.icepay.com/api/payments/{key}
    public function getCheckout(string $checkoutId): CheckoutResponse
    {
        $response = $this->httpClient->get(self::BASE_URL . 'api/payments/' . $checkoutId);
        $this->checkStatusCode($response);
        $json = $response->getBody()->__toString();
        $checkoutResponse = CheckoutResponse::fromResponse($json);
        return $checkoutResponse;
    }

    // GET: https://checkout.icepay.com/api/payments/methods
    public function getPaymentMethods(): array
    {
        $response = $this->httpClient->get(self::BASE_URL . 'api/payments/methods');
        if($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            throw new \Exception("Get payment methods failed with status code: " . $response->getStatusCode());
        }
        $json = $response->getBody()->__toString();
        $methods = json_decode($json, true);
        return $methods;
    }
    protected function callCheckoutApi($url, string $className, ?\JsonSerializable $payload = null): JsonDeserializable
    {
        if (is_subclass_of($className, JsonDeserializable::class)){
            throw new \Exception("Class $className is not a subclass of JsonDeserializable");
        }
        if($payload !== null) {
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
        if($statusCode >= 200 && $statusCode < 300) {
            return true;
        }
        throw new \Exception("Request failed with status code: " . $statusCode);
    }
}
