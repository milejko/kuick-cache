<?php

namespace Tests\Kuick\Cache;

use Kuick\Cache\InMemoryCache;
use Kuick\Cache\LayeredCache;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

/**
 * @covers \Kuick\Cache\LayeredCache
 */
class LayeredCacheTest extends TestCase
{
    public function testIfCacheCanBeSetAndGet(): void
    {
        $cache = new LayeredCache(
            [
                $firstArrayCache = new InMemoryCache(),
                $secondArrayCache = new InMemoryCache(),
            ]
        );
        assertNull($cache->get('inexistent-key'));
        assertFalse($cache->has('inexistent-key'));
        assertTrue($cache->set('/my/key', 'test-value'));
        assertTrue($cache->has('/my/key'));
        assertEquals('test-value', $cache->get('/my/key'));

        assertFalse($firstArrayCache->has('inexistent-key'));
        assertTrue($firstArrayCache->has('/my/key'));
        assertEquals('test-value', $firstArrayCache->get('/my/key'));

        assertFalse($secondArrayCache->has('inexistent-key'));
        assertTrue($secondArrayCache->has('/my/key'));
        assertEquals('test-value', $secondArrayCache->get('/my/key'));
    }

    public function testIfCacheCanBeOverwritten(): void
    {
        $cache = new LayeredCache(
            [
                $firstArrayCache = new InMemoryCache(),
                $secondArrayCache = new InMemoryCache(),
            ]
        );
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->set('foo', 'baz'));
        assertEquals('baz', $cache->get('foo'));

        assertEquals('baz', $firstArrayCache->get('foo'));
        assertEquals('baz', $secondArrayCache->get('foo'));
    }

    public function testIfCacheCanBeDeleted(): void
    {
        $cache = new LayeredCache(
            [
                $firstArrayCache = new InMemoryCache(),
                $secondArrayCache = new InMemoryCache(),
            ]
        );
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->delete('foo'));
        assertNull($cache->get('foo'));

        assertNull($firstArrayCache->get('foo'));
        assertNull($secondArrayCache->get('foo'));
    }

    public function testIfExpiredCacheReturnsNull(): void
    {
        $cache = new LayeredCache(
            [
                $firstArrayCache = new InMemoryCache(),
                $secondArrayCache = new InMemoryCache(),
            ]
        );
        $cache->set('foo', 'bar', 1);
        assertEquals('bar', $cache->get('foo'));
        sleep(1);
        assertNull($cache->get('foo'));

        assertNull($firstArrayCache->get('foo'));
        assertNull($secondArrayCache->get('foo'));
    }

    public function testMultipleSetsAndGetsDeletes(): void
    {
        $cache = new LayeredCache(
            [
                $firstArrayCache = new InMemoryCache(),
                $secondArrayCache = new InMemoryCache(),
            ]
        );
        $sourceArray = [
            'first' => 'first value',
            'second' => 'second value',
            'third' => 'third value',
        ];
        $cache->setMultiple($sourceArray);
        assertEquals($sourceArray, $cache->getMultiple(['first', 'second', 'third']));
        assertTrue($cache->deleteMultiple(['second', 'third']));
        assertEquals(['first' => 'first value'], $cache->getMultiple(['first']));

        assertEquals(['first' => 'first value'], $firstArrayCache->getMultiple(['first']));
        assertEquals(['first' => 'first value'], $secondArrayCache->getMultiple(['first']));
    }

    public function testClear(): void
    {
        $cache = new LayeredCache(
            [
                $firstArrayCache = new InMemoryCache(),
                $secondArrayCache = new InMemoryCache(),
            ]
        );
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

        assertFalse($firstArrayCache->has('foo'));
        assertFalse($firstArrayCache->has('first'));
        assertFalse($firstArrayCache->has('baz'));
        assertFalse($secondArrayCache->has('foo'));
        assertFalse($secondArrayCache->has('first'));
        assertFalse($secondArrayCache->has('baz'));
    }
}
