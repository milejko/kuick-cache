<?php

namespace Tests\Kuick\Cache\Utils;

use Kuick\Cache\ApcuCache;
use Kuick\Cache\ArrayCache;
use Kuick\Cache\FileCache;
use Kuick\Cache\InvalidArgumentException;
use Kuick\Cache\RedisCache;
use Kuick\Cache\Utils\CacheFactory;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertInstanceOf;

/**
 * @covers \Kuick\Cache\Utils\CacheFactory
 */
class CacheFactoryTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        ini_set('apc.enable_cli', '1');

        if (!extension_loaded('apcu')) {
            self::markTestSkipped('APCu extension is not available');
        }
        if (!extension_loaded('redis')) {
            self::markTestSkipped('Redis extension is not available');
        }
    }

    public function testIfFileCacheIsCreated(): void
    {
        $cache = (new CacheFactory())('file:///tmp');
        assertInstanceOf(FileCache::class, $cache);
    }

    public function testIfRedisCacheIsCreated(): void
    {
        $cache = (new CacheFactory())('redis://127.0.0.1');
        assertInstanceOf(RedisCache::class, $cache);
    }

    public function testIfArrayCacheIsCreated(): void
    {
        $cache = (new CacheFactory())('array://');
        assertInstanceOf(ArrayCache::class, $cache);
    }

    public function testIfApcuCacheIsCreated(): void
    {
        $cache = (new CacheFactory())('apcu://');
        assertInstanceOf(ApcuCache::class, $cache);
    }

    public function testIfExceptionIsThrownForInvalidDSN(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new CacheFactory())('inexistent://127.0.0.1');
    }
}
