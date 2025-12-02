<?php

namespace ICEPAY\Checkout;

use ICEPAY\Checkout\Models\Request\Checkout;

class CheckoutClient
{
    const BASE_URL = 'https://checkout.icepay.com/';
    public function __construct(protected $apiKey = '', protected HttpClient $httpClient = new HttpClient())
    {

    }

    public function withApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function withHttpClient(HttpClient $httpClient): self
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    // POST: https://checkout.icepay.com/api/payments
    public function checkout(Checkout $checkout)
    {
        $this->httpClient->post(self::BASE_URL . 'api/payments', $checkout->toArray());
    }

}