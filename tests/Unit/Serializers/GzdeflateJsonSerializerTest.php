<?php

namespace Tests\Unit\Kuick\Cache\Serializers;

use PHPUnit\Framework\TestCase;
use Kuick\Cache\Serializers\GzdeflateJsonSerializer;
use Kuick\Cache\Serializers\SerializerException;
use stdClass;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertIsString;
use function PHPUnit\Framework\assertNotEmpty;

/**
 * @covers \Kuick\Cache\Serializers\GzdeflateJsonSerializer
 */
class GzdeflateJsonSerializerTest extends TestCase
{
    protected GzdeflateJsonSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new GzdeflateJsonSerializer();
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
