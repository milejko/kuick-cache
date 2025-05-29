<?php

namespace Tests\Unit\Kuick\Cache;

use DateInterval;
use Kuick\Cache\NullCache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NullCache::class)]
class NullCacheTest extends TestCase
{
    public function testIfCacheCanBeSetAndGetsNothing(): void
    {
        $cache = new NullCache();
        $this->assertNull($cache->get('inexistent-key'));
        $this->assertFalse($cache->has('inexistent-key'));
        $this->assertTrue($cache->set('/my/key', 'test-value'));
        $this->assertFalse($cache->has('/my/key'));
    }

    public function testIfCacheCanBeDeleted(): void
    {
        $cache = new NullCache();
        $this->assertTrue($cache->set('foo', 'bar'));
        $this->assertTrue($cache->delete('foo'));
        $this->assertNull($cache->get('foo'));
    }

    public function testIfExpiredCacheReturnsNull(): void
    {
        $cache = new NullCache();
        $this->assertTrue($cache->set('bar', 'baz', new DateInterval('PT1S')));
        $this->assertNull($cache->get('bar'));
    }

    public function testMultipleSetsAndGetsDeletes(): void
    {
        $cache = new NullCache();
        $sourceArray = [
            'first' => 'first value',
            'second' => 'second value',
            'third' => 'third value',
        ];
        $this->assertTrue($cache->setMultiple($sourceArray));
        $this->assertEmpty($cache->getMultiple(['first', 'second', 'third']));
        $this->assertTrue($cache->deleteMultiple(['second', 'third']));
        $this->assertEmpty($cache->getMultiple(['first', 'second', 'third']));
    }

    public function testClear(): void
    {
        $cache = new NullCache();
        $this->assertTrue($cache->set('first', 'first value'));
        $cache->setMultiple(
            [
            'foo' => 'baz',
            'baz' => 'bar',
            ]
        );
        $this->assertTrue($cache->clear());
        $this->assertFalse($cache->has('foo'));
        $this->assertFalse($cache->has('first'));
        $this->assertFalse($cache->has('baz'));
    }
}
