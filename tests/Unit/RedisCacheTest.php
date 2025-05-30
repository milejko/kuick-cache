<?php

namespace Tests\Unit\Kuick\Cache;

use Kuick\Cache\RedisCache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Kuick\Redis\RedisMock;
use Psr\SimpleCache\CacheException;
use Redis;
use stdClass;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

#[CoversClass(RedisCache::class)]
class RedisCacheTest extends TestCase
{
    public function testIfCacheCanBeSetAndGet(): void
    {
        $cache = new RedisCache(new RedisMock());
        assertNull($cache->get('inexistent-key'));
        assertFalse($cache->has('inexistent-key'));
        assertTrue($cache->set('/my/key', 'test-value'));
        assertTrue($cache->has('/my/key'));
        assertEquals('test-value', $cache->get('/my/key'));
        $cache->set('foo', new stdClass());
        assertEquals(new stdClass(), $cache->get('foo'));
    }

    public function testIfCacheCanBeOverwritten(): void
    {
        $cache = new RedisCache(new RedisMock());
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->set('foo', 'baz'));
        assertEquals('baz', $cache->get('foo'));
    }

    public function testIfCacheCanBeDeleted(): void
    {
        $cache = new RedisCache(new RedisMock());
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->delete('foo'));
        assertNull($cache->get('foo'));
    }

    public function testIfExpiredCacheReturnsNull(): void
    {
        $cache = new RedisCache(new RedisMock());
        $cache->set('foo', 'bar', 1);
        assertEquals('bar', $cache->get('foo'));
        sleep(1);
        assertNull($cache->get('foo'));
    }

    public function testMultipleSetsAndGetsDeletes(): void
    {
        $cache = new RedisCache(new RedisMock());
        $sourceArray = [
            'first' => 'first value',
            'second' => 'second value',
            'third' => 'third value',
        ];
        $cache->setMultiple($sourceArray);
        assertEquals($sourceArray, $cache->getMultiple(['first', 'second', 'third']));
        assertTrue($cache->deleteMultiple(['second', 'third']));
        assertEquals(['first' => 'first value'], $cache->getMultiple(['first']));
    }

    public function testClear(): void
    {
        $cache = new RedisCache(new RedisMock());
        $cache->set('first', 'first value');
        $cache->setMultiple(
            [
            'foo' => 'baz',
            'baz' => 'bar',
            ]
        );
        assertTrue($cache->has('foo'));
        assertTrue($cache->has('first'));
        assertTrue($cache->has('baz'));
        assertTrue($cache->clear());
        assertFalse($cache->has('foo'));
        assertFalse($cache->has('first'));
        assertFalse($cache->has('baz'));
    }
    public function testIfMessedUpCacheReturnsNull(): void
    {
        $cache = new RedisCache($redisMock = new RedisMock());
        $redisMock->set('foo', null);
        assertNull($cache->get('foo'));
    }

    public function testRealRedisGetThrowsException(): void
    {
        $cache = new RedisCache(new Redis());
        //redis unavailable
        $this->expectException(CacheException::class);
        $cache->get('inexistent-key');
    }

    public function testRealRedisSetThrowsException(): void
    {
        $cache = new RedisCache(new Redis());
        //redis unavailable
        $this->expectException(CacheException::class);
        $cache->set('foo', 'bar');
    }

    public function testRealRedisHasThrowsException(): void
    {
        $cache = new RedisCache(new Redis());
        //redis unavailable
        $this->expectException(CacheException::class);
        $cache->has('foo');
    }

    public function testRealRedisDeleteThrowsException(): void
    {
        $cache = new RedisCache(new Redis());
        //redis unavailable
        $this->expectException(CacheException::class);
        $cache->delete('foo');
    }

    public function testRealRedisClearThrowsException(): void
    {
        $cache = new RedisCache(new Redis());
        //redis unavailable
        $this->expectException(CacheException::class);
        $cache->clear();
    }

    public function testBrokenCacheValueValidation(): void
    {
        $redis = new RedisMock();
        $redis->set('foo', new stdClass());
        $cache = new RedisCache($redis);
        $this->expectException(CacheException::class);
        $cache->get('foo');
    }
}
