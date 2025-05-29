<?php

namespace Tests\Unit\Kuick\Cache;

use Kuick\Cache\ApcuCache;
use Kuick\Cache\InvalidArgumentException;
use Kuick\Cache\RedisCache;
use Kuick\Cache\CacheFactory;
use Kuick\Cache\DbalCache;
use Kuick\Cache\FilesystemCache;
use Kuick\Cache\InMemoryCache;
use Kuick\Cache\NullCache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertInstanceOf;

#[CoversClass(CacheFactory::class)]
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

    public function testIfNullCacheIsCreated(): void
    {
        $cache = (new CacheFactory())('null://');
        assertInstanceOf(NullCache::class, $cache);
    }

    public function testIfFileCacheIsCreated(): void
    {
        $cache = (new CacheFactory())('file:///tmp');
        assertInstanceOf(FilesystemCache::class, $cache);
    }

    public function testIfRedisCacheIsCreated(): void
    {
        $cache = (new CacheFactory())('redis://127.0.0.1');
        assertInstanceOf(RedisCache::class, $cache);
    }

    public function testIfInMemoryCacheIsCreated(): void
    {
        $cache = (new CacheFactory())('in-memory://');
        assertInstanceOf(InMemoryCache::class, $cache);
    }

    public function testIfApcuCacheIsCreated(): void
    {
        $cache = (new CacheFactory())('apcu://');
        assertInstanceOf(ApcuCache::class, $cache);
    }

    public function testIfDbalCacheIsCreated(): void
    {
        $cache = (new CacheFactory())('pdo-sqlite://:memory:');
        assertInstanceOf(DbalCache::class, $cache);
    }

    public function testIfExceptionIsThrownForInvalidDSN(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new CacheFactory())('inexistent://127.0.0.1');
    }
}
