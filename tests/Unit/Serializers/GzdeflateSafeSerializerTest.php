<?php

namespace Tests\Unit\Kuick\Cache\Serializers;

use PHPUnit\Framework\TestCase;
use Kuick\Cache\Serializers\GzdeflateSafeSerializer;
use Kuick\Cache\Serializers\SerializerException;
use stdClass;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertIsString;
use function PHPUnit\Framework\assertNotEmpty;

/**
 * @covers \Kuick\Cache\Serializers\GzdeflateSafeSerializer
 */
class GzdeflateSafeSerializerTest extends TestCase
{
    protected GzdeflateSafeSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new GzdeflateSafeSerializer();
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
