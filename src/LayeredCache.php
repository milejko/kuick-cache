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

class LayeredCache extends AbstractCache implements CacheInterface
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
        $missedBackends = [];
        foreach ($this->orderedBackends as $backend) {
            $value = $backend->get($key);
            // cache miss
            if (null === $value) {
                $missedBackends[] = $backend;
                continue;
            }
            // cache the value in all backends that missed
            foreach ($missedBackends as $missedBackend) {
                $missedBackend->set($key, $value);
            }
            // return cache hit
            return $value;
        }
        return $default;
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
     */
    public function has(string $key): bool
    {
        $missedBackends = [];
        foreach ($this->orderedBackends as $backend) {
            // cache missed
            if (false === $backend->has($key)) {
                $missedBackends[] = $backend;
                continue;
            }
            // cache the value in all backends that missed
            foreach ($missedBackends as $missedBackend) {
                $missedBackend->set($key, $backend->get($key));
            }
            return true;
        }
        return false;
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

    public function clear(): bool
    {
        $result = true;
        foreach ($this->orderedBackends as $backend) {
            $result = $result && $backend->clear();
        }
        return $result;
    }
}
