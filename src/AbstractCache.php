<?php

/**
 * Kuick Cache (https://github.com/milejko/kuick-cache)
 *
 * @link      https://github.com/milejko/kuick-cache
 * @copyright Copyright (c) 2010-2025 Mariusz Miłejko (mariusz@milejko.pl)
 * @license   https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\Cache;

use DateInterval;
use Psr\SimpleCache\CacheInterface;

abstract class AbstractCache implements CacheInterface
{
    private const MAX_KEY_LENGTH = 512;

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
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
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $result = true;
        foreach ($keys as $key) {
            $result = $result && $this->delete($key);
        }
        return $result;
    }

    protected function ttlToInt(null|int|DateInterval $ttl = null): int
    {
        if (null === $ttl) {
            return 0;
        }
        if ($ttl instanceof DateInterval) {
            return $ttl->s;
        }
        return $ttl;
    }

    protected function sanitizeKey(string $key): string
    {
        return urlencode($key);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function validateKey(string $key): void
    {
        $keyLength = strlen($key);
        if (0 === $keyLength) {
            throw new InvalidArgumentException('Empty key is not allowed');
        }
        if ($keyLength > self::MAX_KEY_LENGTH) {
            throw new InvalidArgumentException('Key is too long');
        }
    }
}
