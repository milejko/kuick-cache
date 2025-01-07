<?php

namespace Tests\Unit\Kuick\Cache;

use Doctrine\DBAL\DriverManager;
use Kuick\Cache\InvalidArgumentException;
use Kuick\Cache\DbalCache;
use PHPUnit\Framework\TestCase;
use stdClass;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

/**
 * @covers \Kuick\Cache\DbalCache
 */
class DbalCacheTest extends TestCase
{
    public function testIfCacheCanBeSetAndGet(): void
    {
        $dbal = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
        ]);
        $cache = new DbalCache($dbal);
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
        $dbal = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
        ]);
        $cache = new DbalCache($dbal);
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->set('foo', 'baz'));
        assertEquals('baz', $cache->get('foo'));
    }

    public function testIfCacheCanBeDeleted(): void
    {
        $dbal = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
        ]);
        $cache = new DbalCache($dbal);
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->delete('foo'));
        assertNull($cache->get('foo'));
    }

    public function testIfExpiredCacheReturnsNull(): void
    {
        $dbal = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
        ]);
        $cache = new DbalCache($dbal);
        $cache->set('foo', 'bar', 1);
        assertEquals('bar', $cache->get('foo'));
        sleep(1);
        assertNull($cache->get('foo'));
    }

    public function testMultipleSetsAndGetsDeletes(): void
    {
        $dbal = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
        ]);
        $cache = new DbalCache($dbal);
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
        $dbal = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
        ]);
        $cache = new DbalCache($dbal);
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

    public function testIfKeyToShortThrowsException(): void
    {
        $dbal = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
        ]);
        $cache = new DbalCache($dbal);
        //key to short
        $this->expectException(InvalidArgumentException::class);
        $cache->set('', 'bar');
    }

    public function testIfKeyTooLongThrowsException(): void
    {
        $dbal = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
        ]);
        $cache = new DbalCache($dbal);
        $this->expectException(InvalidArgumentException::class);
        $cache->set('512+character-key-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'bar');
    }

    public function testIfOnceCreatedDbDoesntNeedToBeRecreated(): void
    {
        $dbal = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'path' => '/tmp/test.db',
        ]);
        $cache = new DbalCache($dbal);
        $cache = new DbalCache($dbal);

        assertTrue($cache->set('foo', 'bar'));
    }
}
