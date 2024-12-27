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
use Kuick\Cache\Utils\CacheValueSerializer;
use Psr\SimpleCache\CacheInterface;

class ApcuCache implements CacheInterface
{
    public function __construct()
    {
        function_exists('apcu_enabled') && apcu_enabled() || throw new CacheException('APCu is not enabled for ' . PHP_SAPI);
    }

    /**
     * @throws CacheException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }
        return (new CacheValueSerializer())->unserialize(apcu_fetch($key));
    }

    /**
     * @throws CacheException
     */
    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $ttlSeconds = ($ttl instanceof DateInterval) ? $ttl->s : $ttl;
        return apcu_store($key, (new CacheValueSerializer())->serialize($value, $ttlSeconds ?? 0), $ttlSeconds ?? 0);
    }

    /**
     * @throws CacheException
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
     */
    public function has(string $key): bool
    {
        return apcu_exists($key);
    }

    /**
     * @throws CacheException
     */
    public function delete(string $key): bool
    {
        return apcu_delete($key) !== false;
    }

    /**
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

    /**
     * @throws CacheException
     */
    public function clear(): bool
    {
        return apcu_clear_cache();
    }
}
