<?php

namespace Tests\Unit\Kuick\Cache\Serializers;

use PHPUnit\Framework\TestCase;
use Kuick\Cache\Serializers\GzipSerializer;
use Kuick\Cache\Serializers\SerializerException;
use stdClass;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertIsString;
use function PHPUnit\Framework\assertNotEmpty;

/**
 * @covers \Kuick\Cache\Serializers\GzipSerializer
 */
class GzdeflateSafeSerializerTest extends TestCase
{
    protected GzipSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new GzipSerializer();
    }

    public function testIfSerializationWorks(): void
    {
        $data = ['key' => 'value', 'key2' => new stdClass()];
        $serialized = $this->serializer->serialize($data);
        assertIsString($serialized);
        assertNotEmpty($serialized);
        $unserializedData = $this->serializer->unserialize($serialized);
        assertEquals($data, $unserializedData);
    }

    public function testIfBrokenDataThrowsException(): void
    {
        $this->expectException(SerializerException::class);
        $this->serializer->unserialize('broken-data');
    }
}
