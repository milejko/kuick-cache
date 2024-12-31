<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick)
 *
 * @link      https://github.com/milejko/kuick
 * @copyright Copyright (c) 2010-2024 Mariusz Miłejko (mariusz@milejko.pl)
 * @license   https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\Cache;

use DateInterval;
use FilesystemIterator;
use GlobIterator;
use Psr\SimpleCache\CacheInterface;

class FileCache implements CacheInterface
{
    private const MIN_KEY_LENGTH = 1;
    private const MAX_KEY_LENGTH = 255;

    public function __construct(private string $filesystemCacheDir)
    {
        if (!file_exists($filesystemCacheDir)) {
            mkdir($filesystemCacheDir, 0777, true);
        }
        if (!is_dir($filesystemCacheDir) || !is_writable($filesystemCacheDir)) {
            throw new CacheException('Path: ' . dirname($filesystemCacheDir) . ' is not writeable');
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheFilePath = $this->calculateFilePath($key);
        if (!file_exists($cacheFilePath)) {
            return $default;
        }
        $fileContents = file_get_contents($cacheFilePath);
        $contents = (new Serializer())->unserialize(false === $fileContents ? '' : $fileContents);
        //value non existent or expired
        if (null === $contents) {
            return $default;
        }
        return $contents;
    }

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $cacheFilePath = $this->calculateFilePath($key);
        file_put_contents($cacheFilePath, (new Serializer())->serialize($value, $ttl));
        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     * @param array<string, string> $values
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        $result = true;
        foreach ($values as $key => $value) {
            $result = $result && $this->set($key, $value, $ttl);
        }
        return $result;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
    {
        return null !== $this->get($key);
    }

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function delete(string $key): bool
    {
        return unlink($this->calculateFilePath($key));
    }

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $result = true;
        foreach ($keys as $key) {
            $result = $result && $this->delete($key);
        }
        return $result;
    }

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }
        return $values;
    }

    public function clear(): bool
    {
        //GlobIterator is the best performing directory browser for PHP
        $directoryIterator = new GlobIterator($this->filesystemCacheDir . DIRECTORY_SEPARATOR . '*', FilesystemIterator::KEY_AS_FILENAME);
        foreach ($directoryIterator as $cacheFileName) {
            unlink($cacheFileName);
        }
        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function calculateFilePath(string $key): string
    {
        $keyLength = strlen($key);
        if ($keyLength > self::MAX_KEY_LENGTH || $keyLength < self::MIN_KEY_LENGTH) {
            throw new InvalidArgumentException('Cache key must be between: ' . self::MIN_KEY_LENGTH . ' and ' . self::MAX_KEY_LENGTH . ' characters');
        }
        $encodedKey = urlencode($key);
        return $this->filesystemCacheDir . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . $encodedKey;
    }
}
