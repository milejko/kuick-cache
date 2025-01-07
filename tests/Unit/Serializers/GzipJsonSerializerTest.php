<?php

namespace Tests\Unit\Kuick\Cache\Serializers;

use PHPUnit\Framework\TestCase;
use Kuick\Cache\Serializers\GzipJsonSerializer;
use Kuick\Cache\Serializers\SerializerException;
use stdClass;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertIsString;
use function PHPUnit\Framework\assertNotEmpty;

/**
 * @covers \Kuick\Cache\Serializers\GzipJsonSerializer
 */
class GzipJsonSerializerTest extends TestCase
{
    protected GzipJsonSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new GzipJsonSerializer();
    }

    public function testIfSerializationWorks(): void
    {
        $data = ['key' => 'value', 'key2' => new stdClass()];
        $serialized = $this->serializer->serialize($data);
        assertIsString($serialized);
        assertNotEmpty($serialized);
        $unserializedData = $this->serializer->unserialize($serialized);
        // WARNING: json serialized does not preserve object types
        assertEquals(['key' => 'value', 'key2' => []], $unserializedData);
    }

    public function testIfBrokenDataThrowsException(): void
    {
        $this->expectException(SerializerException::class);
        $this->serializer->unserialize('broken-data');
    }
}
