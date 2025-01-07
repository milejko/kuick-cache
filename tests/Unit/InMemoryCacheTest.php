<?php

namespace Tests\Unit\Kuick\Cache;

use DateInterval;
use Kuick\Cache\InMemoryCache;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use stdClass;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

/**
 * @covers \Kuick\Cache\InMemoryCache
 * @covers \Kuick\Cache\AbstractCache
 */
class InMemoryCacheTest extends TestCase
{
    public function testIfCacheCanBeSetAndGet(): void
    {
        $cache = new InMemoryCache();
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
        $cache = new InMemoryCache();
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->set('foo', 'baz'));
        assertEquals('baz', $cache->get('foo'));
    }

    public function testIfCacheCanBeDeleted(): void
    {
        $cache = new InMemoryCache();
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->delete('foo'));
        assertNull($cache->get('foo'));
    }

    public function testIfExpiredCacheReturnsNull(): void
    {
        $cache = new InMemoryCache();
        $cache->set('foo', 'bar', 1);
        $cache->set('bar', 'baz', new DateInterval('PT1S'));
        assertEquals('bar', $cache->get('foo'));
        assertEquals('baz', $cache->get('bar'));
        sleep(1);
        assertNull($cache->get('foo'));
        assertNull($cache->get('bar'));
    }

    public function testMultipleSetsAndGetsDeletes(): void
    {
        $cache = new InMemoryCache();
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
        $cache = new InMemoryCache();
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

    public function testIfKeyTooLongThrowsException(): void
    {
        $cache = new InMemoryCache();
        $this->expectException(InvalidArgumentException::class);
        $cache->set('512+character-key-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'bar');
    }

    public function testIfKeyTooShortThrowsException(): void
    {
        $cache = new InMemoryCache();
        $this->expectException(InvalidArgumentException::class);
        $cache->set('', 'bar');
    }
}
