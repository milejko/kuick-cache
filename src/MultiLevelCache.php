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
use Psr\SimpleCache\CacheInterface;

class MultiLevelCache implements CacheInterface
{
    /**
     * @param array<CacheInterface> $orderedBackends
     */
    public function __construct(private array $orderedBackends)
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        foreach ($this->orderedBackends as $backend) {
            $value = $backend->get($key, $default);
            if (null !== $value) {
                return $value;
            }
        }
        return null;
    }

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $result = true;
        foreach ($this->orderedBackends as $backend) {
            $result = $result && $backend->set($key, $value, $ttl);
        }
        return $result;
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
        $result = true;
        foreach ($this->orderedBackends as $backend) {
            $result = $result && $backend->delete($key);
        }
        return $result;
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
        $result = true;
        foreach ($this->orderedBackends as $backend) {
            $result = $result && $backend->clear();
        }
        return $result;
    }
}
