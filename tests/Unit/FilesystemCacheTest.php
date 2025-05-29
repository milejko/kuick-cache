<?php

namespace Tests\Unit\Kuick\Cache;

use Kuick\Cache\CacheException;
use Kuick\Cache\FilesystemCache;
use Kuick\Cache\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

/**
 * @SuppressWarnings(ShortVariable)
 */
#[CoversClass(FilesystemCache::class)]
class FilesystemCacheTest extends TestCase
{
    private static string $cacheDir;

    public static function setUpBeforeClass(): void
    {
        self::$cacheDir = dirname(__DIR__) . '/../Mocks/MockProjectDir/var/cache/test-cache';
        $fs = new Filesystem();
        $fs->remove(self::$cacheDir);
    }

    public static function tearDownAfterClass(): void
    {
        $fs = new Filesystem();
        $fs->remove(dirname(__DIR__) . '/../Mocks');
    }

    public function testIfCacheCanBeSetAndGet(): void
    {
        $cache = new FilesystemCache(self::$cacheDir);
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
        $cache = new FilesystemCache(self::$cacheDir);
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->set('foo', 'baz'));
        assertEquals('baz', $cache->get('foo'));
    }

    public function testIfCacheCanBeDeleted(): void
    {
        $cache = new FilesystemCache(self::$cacheDir);
        assertTrue($cache->set('foo', 'bar'));
        assertEquals('bar', $cache->get('foo'));
        assertTrue($cache->delete('foo'));
        assertNull($cache->get('foo'));
    }

    public function testIfExpiredCacheReturnsNull(): void
    {
        $cache = new FilesystemCache(self::$cacheDir);
        $cache->set('foo', 'bar', 1);
        assertEquals('bar', $cache->get('foo'));
        sleep(1);
        assertNull($cache->get('foo'));
    }

    public function testMultipleSetsAndGetsDeletes(): void
    {
        $cache = new FilesystemCache(self::$cacheDir);
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
        $cache = new FilesystemCache(self::$cacheDir);
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

    public function testIfSetToInvalidDirectoryThrowsException(): void
    {
        file_put_contents(self::$cacheDir . '/not-a-dir', 'some content');
        $this->expectException(CacheException::class);
        new FilesystemCache(self::$cacheDir . '/not-a-dir');
    }

    public function testIfKeyTooLongThrowsException(): void
    {
        $cache = new FilesystemCache(self::$cacheDir);
        $this->expectException(InvalidArgumentException::class);
        $cache->set('512+character-key-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'bar');
    }

    public function testIfSettingCacheToInexistentDirThrowsException(): void
    {
        $cache = new FilesystemCache(self::$cacheDir);
        $fs = new Filesystem();
        $fs->remove(self::$cacheDir);
        $this->expectException(CacheException::class);
        $cache->set('foo', 'bar');
    }
}
