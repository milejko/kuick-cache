<?php

namespace Tests\Unit\Kuick\Cache\Serializers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Kuick\Cache\Serializers\PhpSerializer;
use Kuick\Cache\Serializers\SerializerException;
use stdClass;

#[CoversClass(PhpSerializer::class)]
class PhpSerializerTest extends TestCase
{
    protected PhpSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new PhpSerializer();
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

    public function testIfExceptionIsThrownWithCustomErrorHandler(): void
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
        try {
            $this->expectException(SerializerException::class);
            $this->serializer->unserialize('broken-data');
        } finally {
            restore_error_handler();
        }
    }
}
