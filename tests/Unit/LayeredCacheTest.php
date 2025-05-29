<?php

namespace Tests\Unit\Kuick\Cache;

use Kuick\Cache\InMemoryCache;
use Kuick\Cache\LayeredCache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

#[CoversClass(LayeredCache::class)]
class LayeredCacheTest extends TestCase
{
    public function testIfCacheCanBeSetAndGet(): void
    {
        $cache = new LayeredCache(
            [
                $firstCache = new InMemoryCache(),
                $secondCache = new InMemoryCache(),
            ]
        );
        assertNull($cache->get('inexistent-key'));
        assertFalse($cache->has('inexistent-key'));
        assertTrue($cache->set('/my/key', 'test-value'));
        assertTrue($cache->has('/my/key'));
        assertEquals('test-value', $cache->get('/my/key'));

        assertFalse($firstCache->has('inexistent-key'));
        assertTrue($firstCache->has('/my/key'));
        assertEquals('test-value', $firstCache->get('/my/key'));

        assertFalse($secondCache->has('inexistent-key'));
        assertTrue($secondCache->has('/my/key'));
        assertEquals('test-value', $secondCache->get('/my/key'));
    }

    public function testLayeredAvailability(): void
    {
        $cache = new LayeredCache(
            [
                $firstCache = new InMemoryCache(),
                $secondCache = new InMemoryCache(),
            ]
        );
        //second cache has "foo"
        $secondCache->set('foo', 'bar');
        $secondCache->set('bar', 'baz');

        assertNull($firstCache->get('foo'));
        assertFalse($firstCache->has('foo'));
        assertNull($firstCache->get('bar'));
        assertFalse($firstCache->has('bar'));

        //layered cache has "foo"
        assertEquals('bar', $cache->get('foo'));

        //cache was populated
        assertEquals('bar', $firstCache->get('foo'));

        //layered cache has "bar"
        assertTrue($cache->has('bar'));
        //cache was populated
        assertTrue($firstCache->has('bar'));
        assertEquals($firstCache->get('bar'), $secondCache->get('bar'));
    }

    public function testIfCacheCanBeOverwritten(): void
    {
        $cache = new LayeredCache(
            [
                $firstCache = new InMemoryCache(),
                $secondCache = new InMemoryCache(),
            ]
        );
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->set('foo', 'baz'));
        assertEquals('baz', $cache->get('foo'));

        assertEquals('baz', $firstCache->get('foo'));
        assertEquals('baz', $secondCache->get('foo'));
    }

    public function testIfCacheCanBeDeleted(): void
    {
        $cache = new LayeredCache(
            [
                $firstCache = new InMemoryCache(),
                $secondCache = new InMemoryCache(),
            ]
        );
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->delete('foo'));
        assertNull($cache->get('foo'));

        assertNull($firstCache->get('foo'));
        assertNull($secondCache->get('foo'));
    }

    public function testIfExpiredCacheReturnsNull(): void
    {
        $cache = new LayeredCache(
            [
                $firstCache = new InMemoryCache(),
                $secondCache = new InMemoryCache(),
            ]
        );
        $cache->set('foo', 'bar', 1);
        assertEquals('bar', $cache->get('foo'));
        sleep(1);
        assertNull($cache->get('foo'));

        assertNull($firstCache->get('foo'));
        assertNull($secondCache->get('foo'));
    }

    public function testMultipleSetsAndGetsDeletes(): void
    {
        $cache = new LayeredCache(
            [
                $firstCache = new InMemoryCache(),
                $secondCache = new InMemoryCache(),
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

        assertEquals(['first' => 'first value'], $firstCache->getMultiple(['first']));
        assertEquals(['first' => 'first value'], $secondCache->getMultiple(['first']));
    }

    public function testClear(): void
    {
        $cache = new LayeredCache(
            [
                $firstCache = new InMemoryCache(),
                $secondCache = new InMemoryCache(),
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

        assertFalse($firstCache->has('foo'));
        assertFalse($firstCache->has('first'));
        assertFalse($firstCache->has('baz'));
        assertFalse($secondCache->has('foo'));
        assertFalse($secondCache->has('first'));
        assertFalse($secondCache->has('baz'));
    }
}
