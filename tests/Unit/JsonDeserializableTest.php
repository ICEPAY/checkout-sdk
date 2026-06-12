<?php

namespace ICEPAY\Tests\Unit;

use DateTime;
use ICEPAY\Checkout\Models\FinancialStatus;
use ICEPAY\Checkout\Models\Forward;
use ICEPAY\Checkout\Models\JsonDeserializable;
use ICEPAY\Checkout\Models\PaymentMethod;
use ICEPAY\Checkout\Models\Recipient;
use ICEPAY\Checkout\Models\Refund;
use ICEPAY\Checkout\Models\Response\Checkout as CheckoutResponse;
use ICEPAY\Checkout\Models\Response\Forward as ForwardResponse;
use ICEPAY\Checkout\Models\Status;
use ICEPAY\Tests\TestCase;

class JsonDeserializableModel extends JsonDeserializable
{
    public string $stringProp;
    public int $intProp;
    public int $intAsStringProp;
    public bool $boolProp;
    public DateTime $dateTimeProp;
    public JsonDeserializableProperty $dtoProp;
}

class JsonDeserializableProperty extends JsonDeserializable
{
    public string $name;
    public int $value;
}

class NullableModel extends JsonDeserializable
{
    public ?DateTime $nullableDate;
    public ?string $nullableString;
}

class JsonDeserializableTest extends TestCase
{
    public function testFromArray(){
        $data = [
            'stringProp' => 'testString',
            'intProp' => 42,
            'intAsStringProp' => '123',
            'boolProp' => true,
            'dateTimeProp' => '2024-06-01T12:00:00+00:00',
            'dtoProp' => [
                'name' => 'propertyName',
                'value' => 100
            ]
        ];

        $dto = JsonDeserializableModel::fromArray($data);

        $this->assertInstanceOf(JsonDeserializableModel::class, $dto);
        $this->assertEquals('testString', $dto->stringProp);
        $this->assertEquals(42, $dto->intProp);
        $this->assertEquals(123, $dto->intAsStringProp);
        $this->assertTrue($dto->boolProp);
        $this->assertInstanceOf(DateTime::class, $dto->dateTimeProp);
        $this->assertEquals('2024-06-01T12:00:00+00:00', $dto->dateTimeProp->format(DateTime::ATOM));

        $this->assertInstanceOf(JsonDeserializableProperty::class, $dto->dtoProp);
        $this->assertEquals('propertyName', $dto->dtoProp->name);
        $this->assertEquals(100, $dto->dtoProp->value);
    }

    public function testNestedRecipientObjectIsHydratedIntoARecipient(): void
    {
        $forward = ForwardResponse::fromArray([
            'key' => 'fw-1',
            'status' => 'completed',
            'amount' => ['value' => 1000, 'currency' => 'eur'],
            'reference' => 'r1',
            'description' => 'payout',
            'recipient' => ['id' => 'rcpt-1'],
        ]);

        $this->assertInstanceOf(Recipient::class, $forward->recipient);
        $this->assertSame('rcpt-1', $forward->recipient->id);
    }

    public function testNullDateTimeIsAssignedAsNullInsteadOfCrashing(): void
    {
        $model = NullableModel::fromArray(['nullableDate' => null]);

        $this->assertNull($model->nullableDate);
    }

    public function testNullStringStaysNullInsteadOfBecomingEmptyString(): void
    {
        $model = NullableModel::fromArray(['nullableString' => null]);

        $this->assertNull($model->nullableString);
    }

    public function testUnrecognisedStatusFallsBackToUnknownInsteadOfThrowing(): void
    {
        $checkout = CheckoutResponse::fromArray([
            'key' => 'pi-1',
            'status' => 'refunded',
            'amount' => ['value' => 1000, 'currency' => 'eur'],
            'reference' => 'r1',
            'description' => 'order',
        ]);

        $this->assertSame(Status::unknown, $checkout->status);
    }

    public function testFromResponseDecodesAValidJsonString(): void
    {
        $dto = JsonDeserializableProperty::fromResponse('{"name":"propertyName","value":100}');

        $this->assertSame('propertyName', $dto->name);
        $this->assertSame(100, $dto->value);
    }

    public function testFromResponseWithMalformedJsonThrowsAClearException(): void
    {
        $this->expectException(\JsonException::class);

        JsonDeserializableProperty::fromResponse('{not valid json');
    }

    public function testFromResponseWithEmptyStringDoesNotCrash(): void
    {
        $dto = JsonDeserializableProperty::fromResponse('');

        $this->assertInstanceOf(JsonDeserializableProperty::class, $dto);
    }

    public function testUnrecognisedFinancialStatusFallsBackToUnknown(): void
    {
        $this->assertSame(FinancialStatus::unknown, FinancialStatus::fromString('chargeback'));
    }

    public function testCheckoutResponseHydratesPaymentMethod(): void
    {
        $checkout = CheckoutResponse::fromArray([
            'key'           => 'pi-1',
            'status'        => 'pending',
            'amount'        => ['value' => 1000, 'currency' => 'eur'],
            'reference'     => 'r1',
            'description'   => 'order',
            'paymentMethod' => ['type' => 'EPS', 'issuer' => 'AT61000'],
        ]);

        $this->assertInstanceOf(PaymentMethod::class, $checkout->paymentMethod);
        $this->assertSame('EPS', $checkout->paymentMethod->type);
        $this->assertSame('AT61000', $checkout->paymentMethod->issuer);
    }

    public function testCheckoutResponseHydratesRefundsList(): void
    {
        $checkout = CheckoutResponse::fromArray([
            'key'         => 'pi-1',
            'status'      => 'completed',
            'amount'      => ['value' => 1000, 'currency' => 'eur'],
            'reference'   => 'r1',
            'description' => 'order',
            'refunds'     => [
                [
                    'key'         => 'ref-1',
                    'status'      => 'completed',
                    'amount'      => ['value' => 500, 'currency' => 'eur'],
                    'reference'   => 'refund-ref',
                    'description' => 'partial refund',
                ],
            ],
        ]);

        $this->assertIsArray($checkout->refunds);
        $this->assertCount(1, $checkout->refunds);
        $this->assertInstanceOf(Refund::class, $checkout->refunds[0]);
        $this->assertSame('ref-1', $checkout->refunds[0]->key);
    }

    public function testCheckoutResponseHydratesForwardsList(): void
    {
        $checkout = CheckoutResponse::fromArray([
            'key'         => 'pi-1',
            'status'      => 'completed',
            'amount'      => ['value' => 1000, 'currency' => 'eur'],
            'reference'   => 'r1',
            'description' => 'order',
            'forwards'    => [
                [
                    'key'         => 'fw-1',
                    'status'      => 'completed',
                    'amount'      => ['value' => 500, 'currency' => 'eur'],
                    'reference'   => 'fwd-ref',
                    'description' => 'forward',
                    'recipient'   => ['id' => 'rcpt-1'],
                ],
            ],
        ]);

        $this->assertIsArray($checkout->forwards);
        $this->assertCount(1, $checkout->forwards);
        $this->assertInstanceOf(Forward::class, $checkout->forwards[0]);
        $this->assertSame('fw-1', $checkout->forwards[0]->key);
    }

    public function testForwardModelJsonSerialize(): void
    {
        $forward = Forward::fromArray([
            'key'         => 'fw-1',
            'status'      => 'completed',
            'amount'      => ['value' => 500, 'currency' => 'eur'],
            'reference'   => 'ref-001',
            'description' => 'payout',
            'recipient'   => ['id' => 'rcpt-1'],
        ]);

        $encoded = json_decode(json_encode($forward), true);

        $this->assertSame('fw-1', $encoded['key']);
        $this->assertSame('completed', $encoded['status']);
        $this->assertSame(500, $encoded['amount']['value']);
        $this->assertSame('ref-001', $encoded['reference']);
        $this->assertSame('payout', $encoded['description']);
        $this->assertSame(['id' => 'rcpt-1'], $encoded['recipient']);
    }
}
