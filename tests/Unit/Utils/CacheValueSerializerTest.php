<?php

namespace Tests\Kuick\Cache\Utils;

use DateInterval;
use Kuick\Cache\Utils\CacheValueSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNull;

/**
 * @covers \Kuick\Cache\Utils\CacheValueSerializer
 */
class CacheValueSerializerTest extends TestCase
{
    private static string $cacheDir;

    public static function setUpBeforeClass(): void
    {
        self::$cacheDir = dirname(__DIR__) . '/../Mocks/MockProjectDir/var/cache/test-cache';
        $fsystem = new Filesystem();
        $fsystem->remove(self::$cacheDir);
    }

    public function testIfSerializationWorksBothWays(): void
    {
        $cvs = new CacheValueSerializer();
        $serializedValue = $cvs->serialize('test', new DateInterval('PT3600S'));
        assertEquals('test', $cvs->unserialize($serializedValue));
        $anotherSerializedVal = $cvs->serialize('another');
        assertEquals('another', $cvs->unserialize($anotherSerializedVal));
    }

    public function testIfExpiredValueReturnsNull(): void
    {
        $cvs = new CacheValueSerializer();
        $serializedValue = $cvs->serialize('test', 1);
        sleep(1);
        assertNull($cvs->unserialize($serializedValue));
    }
}
