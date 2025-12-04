<?php

namespace ICEPAY\Tests\Unit;

use DateTime;
use ICEPAY\Checkout\Models\JsonDeserializable;
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
}
