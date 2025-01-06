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
use Kuick\Cache\Serializers\SafeSerializer;
use Kuick\Cache\Serializers\SerializerInterface;
use Psr\SimpleCache\CacheInterface;

class FilesystemCache extends AbstractCache implements CacheInterface
{
    private const DEFAULT_BASE_PATH = '/tmp/cache';
    private const TTL_SEPARATOR = '|';
    private const CONTENT_TEMPLATE = '%s' . self::TTL_SEPARATOR . '%s';

    public function __construct(
        private string $basePath = self::DEFAULT_BASE_PATH,
        private SerializerInterface $serializer = new SafeSerializer()
    ) {
        if (!file_exists($basePath)) {
            mkdir($basePath, 0777, true) || throw new CacheException('Could not create directory: ' . $basePath);
        } else {
            is_dir($basePath) || throw new CacheException('Cache path is not a directory: ' . $basePath);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);
        $rawData = @file_get_contents($this->sanitizeKey($key));
        if (false === $rawData) {
            return $default;
        }
        $separatorPosition = strpos($rawData, self::TTL_SEPARATOR);
        $expirationTime = substr($rawData, 0, $separatorPosition);
        if ($expirationTime != 0 && $expirationTime <= time()) {
            $this->delete($key);
            return $default;
        }
        return $this->serializer->unserialize(substr($rawData, $separatorPosition + 1));
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->validateKey($key);
        $intTtl = $this->ttlToInt($ttl);
        $expirationTime = 0 == $intTtl ? 0 : time() + $intTtl;
        if (false === file_put_contents($this->sanitizeKey($key), sprintf(self::CONTENT_TEMPLATE, $expirationTime, $this->serializer->serialize($value)))) {
            throw new CacheException('Could not write to file: ' . $this->sanitizeKey($key));
        }
        return true;
    }

    public function has(string $key): bool
    {
        return null !== $this->get($key);
    }

    public function delete(string $key): bool
    {
        $this->validateKey($key);
        return @unlink($this->sanitizeKey($key));
    }

    public function clear(): bool
    {
        //GlobIterator is the best performing directory browser for PHP
        $directoryIterator = new GlobIterator($this->basePath . DIRECTORY_SEPARATOR . '*', FilesystemIterator::KEY_AS_FILENAME);
        foreach ($directoryIterator as $fileName) {
            @unlink($fileName);
        }
        return true;
    }

    protected function sanitizeKey(string $key): string
    {
        return $this->basePath . '/' . md5($key);
    }
}
