<?php

namespace Tests\Kuick\Cache;

use Kuick\Cache\ArrayCache;
use PHPUnit\Framework\TestCase;
use stdClass;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

/**
 * @covers \Kuick\Cache\ArrayCache
 */
class ArrayCacheTest extends TestCase
{
    public function testIfCacheCanBeSetAndGet(): void
    {
        $cache = new ArrayCache();
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
        $cache = new ArrayCache();
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->set('foo', 'baz'));
        assertEquals('baz', $cache->get('foo'));
    }

    public function testIfCacheCanBeDeleted(): void
    {
        $cache = new ArrayCache();
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->delete('foo'));
        assertNull($cache->get('foo'));
    }

    public function testIfExpiredCacheReturnsNull(): void
    {
        $cache = new ArrayCache();
        $cache->set('foo', 'bar', 1);
        assertEquals('bar', $cache->get('foo'));
        sleep(1);
        assertNull($cache->get('foo'));
    }

    public function testMultipleSetsAndGetsDeletes(): void
    {
        $cache = new ArrayCache();
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
        $cache = new ArrayCache();
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
}
