<?php

namespace Tests\Unit\Kuick\Cache\Serializers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Kuick\Cache\Serializers\GzipSerializer;
use Kuick\Cache\Serializers\SerializerException;
use stdClass;

#[CoversClass(GzipSerializer::class)]
class GzipSerializerTest extends TestCase
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
        $this->assertNotEmpty($serialized);
        $unserializedData = $this->serializer->unserialize($serialized);
        $this->assertEquals($data, $unserializedData);
    }

    public function testIfBrokenDataThrowsException(): void
    {
        $this->expectException(SerializerException::class);
        $this->serializer->unserialize('broken-data');
    }
}
