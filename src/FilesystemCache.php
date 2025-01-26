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
use Kuick\Cache\Serializers\Serializer;
use Kuick\Cache\Serializers\SerializerInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;

class FilesystemCache extends AbstractCache implements CacheInterface
{
    private const DEFAULT_BASE_PATH = '/tmp/cache';
    private const TTL_SEPARATOR = '|';
    private const CONTENT_TEMPLATE = '%s' . self::TTL_SEPARATOR . '%s';

    public function __construct(
        private string $basePath = self::DEFAULT_BASE_PATH,
        private SerializerInterface $serializer = new Serializer()
    ) {
        if (!file_exists($basePath)) {
            mkdir($basePath, 0777, true) || throw new CacheException('Unable to create cache directory: ' . $basePath);
        }
        if (!is_dir($basePath) || !is_writable($basePath)) {
            throw new CacheException('Path is not a writable directory: ' . $basePath);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);
        $rawData = false;
        try {
            $rawData = @file_get_contents($this->sanitizeKey($key));
        } catch (Throwable) {
        }
        if (false === $rawData) {
            return $default;
        }
        $separatorPosition = (int) strpos($rawData, self::TTL_SEPARATOR);
        $expirationTime = substr($rawData, 0, $separatorPosition);
        if ($expirationTime != 0 && $expirationTime <= time()) {
            $this->delete($key);
            return $default;
        }
        return $this->serializer->unserialize(substr($rawData, $separatorPosition + 1));
    }

    /**
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     */
    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->validateKey($key);
        $intTtl = $this->ttlToInt($ttl);
        $expirationTime = 0 == $intTtl ? 0 : time() + $intTtl;
        $result = false;
        try {
            $result = @file_put_contents($this->sanitizeKey($key), sprintf(self::CONTENT_TEMPLATE, $expirationTime, $this->serializer->serialize($value)));
        } catch (Throwable) {
        }
        if (!$result) {
            throw new CacheException('Unable to write cache file');
        }
        return true;
    }

    public function has(string $key): bool
    {
        return null !== $this->get($key);
    }

    /**
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     */
    public function delete(string $key): bool
    {
        $this->validateKey($key);
        try {
            return @unlink($this->sanitizeKey($key));
        } catch (Throwable) {
        }
        return false;
    }

    /**
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     */
    public function clear(): bool
    {
        //GlobIterator is the best performing directory browser for PHP
        $directoryIterator = new GlobIterator($this->basePath . DIRECTORY_SEPARATOR . '*', FilesystemIterator::KEY_AS_FILENAME);
        foreach ($directoryIterator as $fileName) {
            try {
                @unlink($fileName);
            } catch (Throwable) {
            }
        }
        return true;
    }

    protected function sanitizeKey(string $key): string
    {
        return $this->basePath . '/' . parent::sanitizeKey($key);
    }
}
