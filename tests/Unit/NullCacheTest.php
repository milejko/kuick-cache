<?php

namespace Tests\Unit\Kuick\Cache;

use DateInterval;
use Kuick\Cache\NullCache;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;

use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

/**
 * @covers \Kuick\Cache\NullCache
 * @covers \Kuick\Cache\AbstractCache
 */
class NullCacheTest extends TestCase
{
    public function testIfCacheCanBeSetAndGetsNothing(): void
    {
        $cache = new NullCache();
        assertNull($cache->get('inexistent-key'));
        assertFalse($cache->has('inexistent-key'));
        assertTrue($cache->set('/my/key', 'test-value'));
        assertFalse($cache->has('/my/key'));
    }

    public function testIfCacheCanBeDeleted(): void
    {
        $cache = new NullCache();
        assertTrue($cache->set('foo', 'bar'));
        assertTrue($cache->delete('foo'));
        assertNull($cache->get('foo'));
    }

    public function testIfExpiredCacheReturnsNull(): void
    {
        $cache = new NullCache();
        $cache->set('bar', 'baz', new DateInterval('PT1S'));
        assertNull($cache->get('bar'));
    }

    public function testMultipleSetsAndGetsDeletes(): void
    {
        $cache = new NullCache();
        $sourceArray = [
            'first' => 'first value',
            'second' => 'second value',
            'third' => 'third value',
        ];
        assertTrue($cache->setMultiple($sourceArray));
        assertEmpty($cache->getMultiple(['first', 'second', 'third']));
        assertTrue($cache->deleteMultiple(['second', 'third']));
        assertEmpty($cache->getMultiple(['first', 'second', 'third']));
    }

    public function testClear(): void
    {
        $cache = new NullCache();
        $cache->set('first', 'first value');
        $cache->setMultiple(
            [
            'foo' => 'baz',
            'baz' => 'bar',
            ]
        );
        assertTrue($cache->clear());
        assertFalse($cache->has('foo'));
        assertFalse($cache->has('first'));
        assertFalse($cache->has('baz'));
    }
}
