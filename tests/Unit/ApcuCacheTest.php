<?php

namespace Tests\Kuick\Cache;

use Kuick\Cache\ApcuCache;
use Kuick\Cache\CacheException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(ApcuCache::class)]
class ApcuCacheTest extends TestCase
{
    public function setUp(): void
    {
        apcu_clear_cache();
    }

    public function testIfCacheCanBeSetAndGet(): void
    {
        $cache = new ApcuCache();
        $this->assertNull($cache->get('inexistent-key'));
        $this->assertFalse($cache->has('inexistent-key'));
        $this->assertTrue($cache->set('/my/key', 'test-value'));
        $this->assertTrue($cache->has('/my/key'));
        $this->assertEquals('test-value', $cache->get('/my/key'));
        $cache->set('foo', new stdClass());
        $this->assertEquals(new stdClass(), $cache->get('foo'));
    }

    public function testIfCacheCanBeOverwritten(): void
    {
        $cache = new ApcuCache();
        $this->assertTrue($cache->set('foo', 'bar'));
        $this->assertEquals('bar', $cache->get('foo'));
        $this->assertTrue($cache->set('foo', 'baz'));
        $this->assertEquals('baz', $cache->get('foo'));
    }

    public function testIfCacheCanBeDeleted(): void
    {
        $cache = new ApcuCache();
        $this->assertTrue($cache->set('foo', 'bar'));
        $this->assertEquals('bar', $cache->get('foo'));
        $this->assertTrue($cache->delete('foo'));
        $this->assertNull($cache->get('foo'));
    }

    public function testIfExpiredCacheReturnsNull(): void
    {
        $cache = new ApcuCache();
        $cache->set('foo', 'bar', 1);
        $this->assertEquals('bar', $cache->get('foo'));
        sleep(2);
        $this->assertNull($cache->get('foo'));
    }

    public function testMultipleSetsAndGetsDeletes(): void
    {
        $cache = new ApcuCache();
        $sourceArray = [
            'first' => 'first value',
            'second' => 'second value',
            'third' => 'third value',
        ];
        $cache->setMultiple($sourceArray);
        $this->assertEquals($sourceArray, $cache->getMultiple(['first', 'second', 'third']));
        $this->assertTrue($cache->deleteMultiple(['second', 'third']));
        $this->assertEquals(['first' => 'first value'], $cache->getMultiple(['first']));
    }

    public function testClear(): void
    {
        $cache = new ApcuCache();
        $cache->set('first', 'first value');
        $cache->setMultiple(
            [
            'foo' => 'baz',
            'baz' => 'bar',
            ]
        );
        $this->assertTrue($cache->has('foo'));
        $this->assertTrue($cache->has('first'));
        $this->assertTrue($cache->has('baz'));
        $this->assertTrue($cache->clear());
        $this->assertFalse($cache->has('foo'));
        $this->assertFalse($cache->has('first'));
        $this->assertFalse($cache->has('baz'));
    }

    public function testIfMessedUpCacheReturnsNull(): void
    {
        $cache = new ApcuCache();
        apcu_store('foo', new stdClass());
        $this->expectException(CacheException::class);
        $cache->get('foo');
    }
}
