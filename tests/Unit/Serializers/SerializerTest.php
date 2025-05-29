<?php

namespace Tests\Unit\Kuick\Cache\Serializers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Kuick\Cache\Serializers\Serializer;
use Kuick\Cache\Serializers\SerializerException;
use stdClass;

#[CoversClass(Serializer::class)]
class SerializerTest extends TestCase
{
    protected Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new Serializer();
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
