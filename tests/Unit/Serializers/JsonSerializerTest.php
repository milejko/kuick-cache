<?php

namespace Tests\Unit\Kuick\Cache\Serializers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Kuick\Cache\Serializers\JsonSerializer;
use Kuick\Cache\Serializers\SerializerException;
use stdClass;

#[CoversClass(JsonSerializer::class)]
class JsonSerializerTest extends TestCase
{
    protected JsonSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new JsonSerializer();
    }

    public function testIfSerializationWorks(): void
    {
        $data = ['key' => 'value', 'key2' => new stdClass()];
        $serialized = $this->serializer->serialize($data);
        $this->assertNotEmpty($serialized);
        $unserializedData = $this->serializer->unserialize($serialized);
        // WARNING: json serialized does not preserve object types
        $this->assertEquals(['key' => 'value', 'key2' => []], $unserializedData);
    }

    public function testIfSerializingBrokenDataThrowsException(): void
    {
        $this->expectException(SerializerException::class);
        $this->serializer->serialize(["\xB1\x31"]);
    }

    public function testIfUnserializinBrokenDataThrowsException(): void
    {
        $this->expectException(SerializerException::class);
        $this->serializer->unserialize('broken-data');
    }
}
